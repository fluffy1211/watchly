<?php

namespace App\Controller;

use App\Service\PasswordResetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PasswordResetController extends AbstractController
{
    #[Route('/api/password-reset/request', name: 'api_password_reset_request', methods: ['POST'])]
    public function request(
        Request $request,
        PasswordResetService $passwordResetService,
        ValidatorInterface $validator,
        RateLimiterFactory $passwordResetLimiter,
    ): JsonResponse {
        $limit = $passwordResetLimiter->create($request->getClientIp())->consume();
        if (!$limit->isAccepted()) {
            return $this->json(
                ['message' => 'Too many reset requests. Please try again later.'],
                Response::HTTP_TOO_MANY_REQUESTS,
                ['Retry-After' => $limit->getRetryAfter()->getTimestamp() - time()]
            );
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $constraints = new Assert\Collection([
            'email' => [new Assert\NotBlank(), new Assert\Email()],
        ]);

        $violations = $validator->validate($data, $constraints);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }

            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $passwordResetService->request($data['email']);

        // Same generic response whether or not the account exists, to avoid
        // leaking which email addresses are registered.
        return $this->json(
            ['message' => 'If an account exists for this email, a reset link has been sent.'],
            Response::HTTP_ACCEPTED
        );
    }

    #[Route('/api/password-reset/reset', name: 'api_password_reset_reset', methods: ['POST'])]
    public function reset(
        Request $request,
        PasswordResetService $passwordResetService,
        ValidatorInterface $validator,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        $constraints = new Assert\Collection([
            'token' => [new Assert\NotBlank()],
            'password' => [new Assert\NotBlank(), new Assert\Length(min: 8)],
            'password_confirmation' => [new Assert\NotBlank()],
        ]);

        $violations = $validator->validate($data, $constraints);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }

            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($data['password'] !== $data['password_confirmation']) {
            return $this->json(
                ['errors' => ['password_confirmation: Passwords do not match']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $passwordResetService->reset($data['token'], $data['password']);
        } catch (\InvalidArgumentException) {
            return $this->json(
                ['message' => 'Invalid or expired reset token'],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->json(['message' => 'Password updated successfully']);
    }
}
