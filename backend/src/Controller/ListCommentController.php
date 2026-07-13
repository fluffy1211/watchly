<?php

namespace App\Controller;

use App\Entity\CommentReport;
use App\Entity\ListComment;
use App\Repository\CommentReportRepository;
use App\Repository\ListCommentRepository;
use App\Repository\MovieListRepository;
use App\Service\CommentReportService;
use App\Service\ListCommentService;
use App\Service\ListService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ListCommentController extends AbstractController
{
    #[Route('/api/lists/{id}/comments', name: 'api_list_comments_create', methods: ['POST'])]
    public function create(
        int $id,
        Request $request,
        MovieListRepository $listRepo,
        ListService $listService,
        ListCommentService $commentService,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $list = $listRepo->find($id);
        if ($list === null) {
            return $this->json(['message' => 'List not found'], Response::HTTP_NOT_FOUND);
        }

        $user = $security->getUser();
        if (!$listService->canView($list, $user)) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $content = $commentService->validateContent((string) ($data['content'] ?? ''));
        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $comment = new ListComment();
        $comment->setList($list);
        $comment->setAuthor($user);
        $comment->setContent($content);

        $em->persist($comment);
        $em->flush();

        return $this->json([
            'message' => 'Comment added',
            'comment' => $this->formatComment($comment),
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/lists/{id}/comments', name: 'api_list_comments_list', methods: ['GET'])]
    public function list(
        int $id,
        MovieListRepository $listRepo,
        ListCommentRepository $commentRepo,
        ListService $listService,
        Security $security,
    ): JsonResponse {
        $list = $listRepo->find($id);
        if ($list === null) {
            return $this->json(['message' => 'List not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$listService->canView($list, $security->getUser())) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $comments = $commentRepo->findBy(['list' => $list], ['createdAt' => 'ASC']);

        return $this->json(array_map(fn($c) => $this->formatComment($c), $comments));
    }

    #[Route('/api/comments/{id}', name: 'api_comment_delete', methods: ['DELETE'])]
    public function delete(int $id, ListCommentRepository $repo, EntityManagerInterface $em, Security $security): JsonResponse
    {
        $comment = $repo->find($id);
        if ($comment === null) {
            return $this->json(['message' => 'Comment not found'], Response::HTTP_NOT_FOUND);
        }
        if ($comment->getAuthor() !== $security->getUser()) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($comment);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/comments/{id}/report', name: 'api_comment_report', methods: ['POST'])]
    public function report(
        int $id,
        Request $request,
        ListCommentRepository $repo,
        CommentReportRepository $reportRepo,
        CommentReportService $reportService,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $comment = $repo->find($id);
        if ($comment === null) {
            return $this->json(['message' => 'Comment not found'], Response::HTTP_NOT_FOUND);
        }

        $user = $security->getUser();
        if ($comment->getAuthor() === $user) {
            return $this->json(['message' => 'Cannot report your own comment'], Response::HTTP_FORBIDDEN);
        }

        if ($reportRepo->findOneBy(['comment' => $comment, 'reporter' => $user]) !== null) {
            return $this->json(['message' => 'Comment already reported'], Response::HTTP_CONFLICT);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $reason = $reportService->validateReason($data['reason'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $report = new CommentReport();
        $report->setComment($comment);
        $report->setReporter($user);
        $report->setReason($reason);

        $em->persist($report);
        $em->flush();

        return $this->json(['message' => 'Comment reported'], Response::HTTP_CREATED);
    }

    private function formatComment(ListComment $comment): array
    {
        return [
            'id'         => $comment->getId(),
            'content'    => $comment->getContent(),
            'created_at' => $comment->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updated_at' => $comment->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            'author'     => [
                'username' => $comment->getAuthor()->getUsername(),
            ],
        ];
    }
}
