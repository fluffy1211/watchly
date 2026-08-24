<?php

namespace App\Controller;

use App\Repository\CommentReportRepository;
use App\Repository\ReviewReportRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/api/admin/users', name: 'api_admin_users_list', methods: ['GET'])]
    public function list(
        UserRepository $repo,
        Security $security,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $stats = $repo->findAllWithStats();
        $users = $repo->findAll();

        return $this->json(array_map(function ($user) use ($stats) {
            $id = $user->getId();
            $s  = $stats[$id] ?? ['total' => 0, 'watched' => 0, 'favorites' => 0];

            return [
                'id'         => $id,
                'email'      => $user->getEmail(),
                'username'   => $user->getUsername(),
                'roles'      => $user->getRoles(),
                'created_at' => $user->getCreatedAt()?->format(\DateTimeInterface::ATOM),
                'stats'      => [
                    'total_films' => $s['total'],
                    'watched'     => $s['watched'],
                    'favorites'   => $s['favorites'],
                ],
            ];
        }, $users));
    }

    #[Route('/api/admin/users/{id}', name: 'api_admin_users_patch', methods: ['PATCH'])]
    public function patch(
        int $id,
        Request $request,
        UserRepository $repo,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $admin = $security->getUser();
        $user  = $repo->find($id);

        if ($user === null) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if ($user === $admin) {
            return $this->json(['message' => 'Cannot modify your own account'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (!isset($data['roles']) || !is_array($data['roles'])) {
            return $this->json(['message' => 'roles array is required'], Response::HTTP_BAD_REQUEST);
        }

        if (!in_array('ROLE_USER', $data['roles'], true)) {
            return $this->json(['message' => 'ROLE_USER must always be present'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (in_array('ROLE_SUPER_ADMIN', $data['roles'], true)) {
            return $this->json(['message' => 'ROLE_SUPER_ADMIN cannot be granted via this endpoint'], Response::HTTP_FORBIDDEN);
        }

        $currentlyAdmin   = in_array('ROLE_ADMIN', $user->getRoles(), true);
        $requestedAdmin   = in_array('ROLE_ADMIN', $data['roles'], true);
        if ($currentlyAdmin !== $requestedAdmin) {
            $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        }

        $user->setRoles(array_values(array_unique($data['roles'])));
        $em->flush();

        return $this->json([
            'message' => 'User updated',
            'user'    => [
                'id'       => $user->getId(),
                'email'    => $user->getEmail(),
                'username' => $user->getUsername(),
                'roles'    => $user->getRoles(),
            ],
        ]);
    }

    #[Route('/api/admin/users/{id}', name: 'api_admin_users_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        UserRepository $repo,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $admin = $security->getUser();
        $user  = $repo->find($id);

        if ($user === null) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if ($user === $admin) {
            return $this->json(['message' => 'Cannot delete your own account'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/admin/comment-reports', name: 'api_admin_comment_reports_list', methods: ['GET'])]
    public function listCommentReports(CommentReportRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $reports = $repo->findAll();

        return $this->json(array_map(function ($report) {
            $comment = $report->getComment();

            return [
                'id'         => $report->getId(),
                'reason'     => $report->getReason(),
                'created_at' => $report->getCreatedAt()?->format(\DateTimeInterface::ATOM),
                'reporter'   => [
                    'username' => $report->getReporter()->getUsername(),
                ],
                'comment'    => [
                    'id'      => $comment->getId(),
                    'content' => $comment->getContent(),
                    'author'  => [
                        'username' => $comment->getAuthor()->getUsername(),
                    ],
                    'list'    => [
                        'id'    => $comment->getList()->getId(),
                        'title' => $comment->getList()->getTitle(),
                    ],
                ],
            ];
        }, $reports));
    }

    #[Route('/api/admin/comment-reports/{id}', name: 'api_admin_comment_reports_patch', methods: ['PATCH'])]
    public function patchCommentReport(
        int $id,
        Request $request,
        CommentReportRepository $repo,
        EntityManagerInterface $em,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $report = $repo->find($id);
        if ($report === null) {
            return $this->json(['message' => 'Report not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $action = $data['action'] ?? null;

        if (!in_array($action, ['keep', 'delete'], true)) {
            return $this->json(['message' => 'action must be "keep" or "delete"'], Response::HTTP_BAD_REQUEST);
        }

        if ($action === 'keep') {
            $em->remove($report);
            $em->flush();

            return $this->json(['message' => 'Report dismissed']);
        }

        $em->remove($report->getComment());
        $em->flush();

        return $this->json(['message' => 'Comment deleted']);
    }

    #[Route('/api/admin/review-reports', name: 'api_admin_review_reports_list', methods: ['GET'])]
    public function listReviewReports(ReviewReportRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $reports = $repo->findAll();

        return $this->json(array_map(function ($report) {
            $review = $report->getReview();

            return [
                'id'         => $report->getId(),
                'reason'     => $report->getReason(),
                'created_at' => $report->getCreatedAt()?->format(\DateTimeInterface::ATOM),
                'reporter'   => [
                    'username' => $report->getReporter()->getUsername(),
                ],
                'review'     => [
                    'id'      => $review->getId(),
                    'content' => $review->getContent(),
                    'author'  => [
                        'username' => $review->getUser()->getUsername(),
                    ],
                    'film'    => [
                        'id'    => $review->getFilm()->getId(),
                        'title' => $review->getFilm()->getTitle(),
                    ],
                ],
            ];
        }, $reports));
    }

    #[Route('/api/admin/review-reports/{id}', name: 'api_admin_review_reports_patch', methods: ['PATCH'])]
    public function patchReviewReport(
        int $id,
        Request $request,
        ReviewReportRepository $repo,
        EntityManagerInterface $em,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $report = $repo->find($id);
        if ($report === null) {
            return $this->json(['message' => 'Report not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $action = $data['action'] ?? null;

        if (!in_array($action, ['keep', 'delete'], true)) {
            return $this->json(['message' => 'action must be "keep" or "delete"'], Response::HTTP_BAD_REQUEST);
        }

        if ($action === 'keep') {
            $em->remove($report);
            $em->flush();

            return $this->json(['message' => 'Report dismissed']);
        }

        $em->remove($report->getReview());
        $em->flush();

        return $this->json(['message' => 'Review deleted']);
    }
}
