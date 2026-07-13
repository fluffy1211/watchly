<?php

namespace App\Controller;

use App\Entity\ListComment;
use App\Repository\ListCommentRepository;
use App\Repository\MovieListRepository;
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
