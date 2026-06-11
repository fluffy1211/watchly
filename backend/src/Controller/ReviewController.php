<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\UserCollection;
use App\Repository\FilmRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserCollectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReviewController extends AbstractController
{
    #[Route('/api/films/{id}/review', name: 'api_film_review_put', methods: ['PUT'])]
    public function put(
        int $id,
        Request $request,
        FilmRepository $filmRepo,
        ReviewRepository $reviewRepo,
        UserCollectionRepository $collectionRepo,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $film = $filmRepo->findOneBy(['tmdbId' => $id]);
        if ($film === null) {
            return $this->json(['message' => 'Film not found'], Response::HTTP_NOT_FOUND);
        }

        $user = $security->getUser();

        $uc = $collectionRepo->findOneBy(['user' => $user, 'film' => $film]);
        if ($uc === null || $uc->getStatus() !== UserCollection::STATUS_WATCHED) {
            return $this->json(
                ['message' => 'You must have watched this film before reviewing it'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $content = trim((string) ($data['content'] ?? ''));

        if (strlen($content) < 10) {
            return $this->json(['message' => 'Review must be at least 10 characters'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if (strlen($content) > 2000) {
            return $this->json(['message' => 'Review must be at most 2000 characters'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $review = $reviewRepo->findOneByUserAndFilm($user, $film);
        $isNew  = $review === null;

        if ($isNew) {
            $review = new Review();
            $review->setUser($user);
            $review->setFilm($film);
            $em->persist($review);
        }

        $review->setContent($content);

        if (!$isNew) {
            $review->onPreUpdate();
        }

        $em->flush();

        return $this->json([
            'message' => 'Review saved',
            'review'  => $this->formatReview($review),
        ], $isNew ? Response::HTTP_CREATED : Response::HTTP_OK);
    }

    #[Route('/api/films/{id}/reviews', name: 'api_film_reviews_get', methods: ['GET'])]
    public function list(
        int $id,
        FilmRepository $filmRepo,
        ReviewRepository $reviewRepo,
    ): JsonResponse {
        $film = $filmRepo->findOneBy(['tmdbId' => $id]);
        if ($film === null) {
            return $this->json(['message' => 'Film not found'], Response::HTTP_NOT_FOUND);
        }

        $reviews = $reviewRepo->findByFilmWithUser($film);

        return $this->json(array_map(fn($r) => [
            'id'         => $r->getId(),
            'content'    => $r->getContent(),
            'created_at' => $r->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updated_at' => $r->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            'user'       => [
                'id'       => $r->getUser()->getId(),
                'username' => $r->getUser()->getUsername(),
            ],
        ], $reviews));
    }

    #[Route('/api/films/{id}/review', name: 'api_film_review_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        FilmRepository $filmRepo,
        ReviewRepository $reviewRepo,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $film = $filmRepo->findOneBy(['tmdbId' => $id]);
        if ($film === null) {
            return $this->json(['message' => 'Film not found'], Response::HTTP_NOT_FOUND);
        }

        $user   = $security->getUser();
        $review = $reviewRepo->findOneByUserAndFilm($user, $film);

        if ($review === null) {
            return $this->json(['message' => 'Review not found'], Response::HTTP_NOT_FOUND);
        }

        if ($review->getUser() !== $user) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($review);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function formatReview(Review $review): array
    {
        return [
            'id'         => $review->getId(),
            'content'    => $review->getContent(),
            'created_at' => $review->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updated_at' => $review->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }
}
