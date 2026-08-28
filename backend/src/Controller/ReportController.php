<?php

namespace App\Controller;

use App\Entity\Reportable;
use App\Enum\ReportAction;
use App\Exception\DuplicateReportException;
use App\Exception\InvalidReportReasonException;
use App\Exception\SelfReportException;
use App\Repository\ListCommentRepository;
use App\Repository\ReportRepository;
use App\Repository\ReviewRepository;
use App\Serializer\ReportViewNormalizer;
use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReportController extends AbstractController
{
    #[Route('/api/comments/{id}/report', name: 'api_comment_report', methods: ['POST'])]
    public function reportComment(
        int $id,
        Request $request,
        ListCommentRepository $comments,
        ReportService $service,
        Security $security,
    ): JsonResponse {
        $comment = $comments->find($id);
        if ($comment === null) {
            return $this->json(['message' => 'Comment not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->fileReport($comment, $request, $service, $security, 'Comment reported');
    }

    #[Route('/api/reviews/{id}/report', name: 'api_review_report', methods: ['POST'])]
    public function reportReview(
        int $id,
        Request $request,
        ReviewRepository $reviews,
        ReportService $service,
        Security $security,
    ): JsonResponse {
        $review = $reviews->find($id);
        if ($review === null) {
            return $this->json(['message' => 'Review not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->fileReport($review, $request, $service, $security, 'Review reported');
    }

    #[Route('/api/admin/reports', name: 'api_admin_reports_list', methods: ['GET'])]
    public function list(ReportRepository $reports, ReportViewNormalizer $view): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->json(array_map(
            $view->normalize(...),
            $reports->findAllForModeration(),
        ));
    }

    #[Route('/api/admin/reports/{id}', name: 'api_admin_reports_resolve', methods: ['PATCH'])]
    public function resolve(
        int $id,
        Request $request,
        ReportRepository $reports,
        ReportService $service,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $report = $reports->find($id);
        if ($report === null) {
            return $this->json(['message' => 'Report not found'], Response::HTTP_NOT_FOUND);
        }

        $data   = json_decode($request->getContent(), true) ?? [];
        $action = ReportAction::tryFrom((string) ($data['action'] ?? ''));
        if ($action === null) {
            return $this->json(['message' => 'action must be "keep" or "delete"'], Response::HTTP_BAD_REQUEST);
        }

        $service->resolve($report, $action);

        return $this->json([
            'message' => $action === ReportAction::Delete ? 'Content deleted' : 'Report dismissed',
        ]);
    }

    private function fileReport(
        Reportable $target,
        Request $request,
        ReportService $service,
        Security $security,
        string $okMessage,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $service->file($security->getUser(), $target, $data['reason'] ?? null);
        } catch (SelfReportException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (DuplicateReportException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (InvalidReportReasonException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json(['message' => $okMessage], Response::HTTP_CREATED);
    }
}
