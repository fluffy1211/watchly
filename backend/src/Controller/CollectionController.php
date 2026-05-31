<?php

namespace App\Controller;

use App\Entity\UserCollection;
use App\Repository\FilmRepository;
use App\Repository\UserCollectionRepository;
use App\Service\CollectionService;
use App\Service\FilmService;
use App\Service\TMDBService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CollectionController extends AbstractController
{
    #[Route('/api/collection/add', name: 'api_collection_add', methods: ['POST'])]
    public function add(
        Request $request,
        TMDBService $tmdb,
        FilmService $filmService,
        FilmRepository $filmRepo,
        UserCollectionRepository $repo,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        if (!isset($data['tmdb_id']) || !is_int($data['tmdb_id'])) {
            return $this->json(['message' => 'tmdb_id is required and must be an integer'], Response::HTTP_BAD_REQUEST);
        }

        $user = $security->getUser();
        $tmdbId = $data['tmdb_id'];

        $existingFilm = $filmRepo->findOneBy(['tmdbId' => $tmdbId]);
        if ($existingFilm !== null && $repo->findOneBy(['user' => $user, 'film' => $existingFilm]) !== null) {
            return $this->json(['message' => 'Film already in collection'], Response::HTTP_CONFLICT);
        }

        $tmdbData = $tmdb->getMovieById($tmdbId);
        if (empty($tmdbData)) {
            return $this->json(['message' => 'Film not found on TMDB'], Response::HTTP_NOT_FOUND);
        }

        $film = $filmService->findOrCreate($tmdbData, $em);

        if ($repo->findOneBy(['user' => $user, 'film' => $film]) !== null) {
            return $this->json(['message' => 'Film already in collection'], Response::HTTP_CONFLICT);
        }

        $filmService->syncGenres($film, $tmdbData['genres'] ?? [], $em);

        $uc = new UserCollection();
        $uc->setUser($user);
        $uc->setFilm($film);

        $em->persist($uc);
        $em->flush();

        return $this->json([
            'message'    => 'Film added to collection',
            'collection' => $this->formatEntry($uc),
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/collection', name: 'api_collection_list', methods: ['GET'])]
    public function list(
        Request $request,
        UserCollectionRepository $repo,
        Security $security,
    ): JsonResponse {
        $user = $security->getUser();
        $status   = $request->query->get('status');
        $favorite = $request->query->has('favorite') ? filter_var($request->query->get('favorite'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

        if ($status !== null && !in_array($status, [UserCollection::STATUS_WATCHLIST, UserCollection::STATUS_WATCHED], true)) {
            return $this->json(['message' => 'Invalid status filter'], Response::HTTP_BAD_REQUEST);
        }

        $entries = $repo->findByUserWithFilters($user, $status, $favorite);

        return $this->json(array_map(fn($uc) => $this->formatFullEntry($uc), $entries));
    }

    #[Route('/api/collection/{id}/status', name: 'api_collection_status', methods: ['PATCH'])]
    public function updateStatus(
        int $id,
        Request $request,
        UserCollectionRepository $repo,
        CollectionService $service,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $uc = $repo->find($id);
        if ($uc === null) {
            return $this->json(['message' => 'Collection entry not found'], Response::HTTP_NOT_FOUND);
        }
        if ($uc->getUser() !== $security->getUser()) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        if (!isset($data['status'])) {
            return $this->json(['message' => 'status is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $service->setStatus($uc, $data['status']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $em->flush();

        return $this->json([
            'message'    => 'Status updated',
            'collection' => [
                'id'         => $uc->getId(),
                'status'     => $uc->getStatus(),
                'is_favorite' => $uc->isFavorite(),
                'rating'     => $uc->getRating(),
                'watched_at' => $uc->getWatchedAt()?->format(\DateTimeInterface::ATOM),
            ],
        ]);
    }

    #[Route('/api/collection/{id}/favorite', name: 'api_collection_favorite', methods: ['PATCH'])]
    public function updateFavorite(
        int $id,
        Request $request,
        UserCollectionRepository $repo,
        CollectionService $service,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $uc = $repo->find($id);
        if ($uc === null) {
            return $this->json(['message' => 'Collection entry not found'], Response::HTTP_NOT_FOUND);
        }
        if ($uc->getUser() !== $security->getUser()) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        if (!array_key_exists('is_favorite', $data)) {
            return $this->json(['message' => 'is_favorite is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $service->setFavorite($uc, (bool) $data['is_favorite']);
        } catch (\LogicException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->flush();

        return $this->json([
            'message'    => 'Favorite updated',
            'collection' => [
                'id'          => $uc->getId(),
                'status'      => $uc->getStatus(),
                'is_favorite' => $uc->isFavorite(),
            ],
        ]);
    }

    #[Route('/api/collection/{id}/rating', name: 'api_collection_rating', methods: ['PATCH'])]
    public function updateRating(
        int $id,
        Request $request,
        UserCollectionRepository $repo,
        CollectionService $service,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $uc = $repo->find($id);
        if ($uc === null) {
            return $this->json(['message' => 'Collection entry not found'], Response::HTTP_NOT_FOUND);
        }
        if ($uc->getUser() !== $security->getUser()) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        if (!array_key_exists('rating', $data)) {
            return $this->json(['message' => 'rating is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $service->setRating($uc, $data['rating'] !== null ? (int) $data['rating'] : null);
        } catch (\LogicException|\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->flush();

        return $this->json([
            'message'    => 'Rating updated',
            'collection' => [
                'id'     => $uc->getId(),
                'rating' => $uc->getRating(),
            ],
        ]);
    }

    #[Route('/api/collection/{id}', name: 'api_collection_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        UserCollectionRepository $repo,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $uc = $repo->find($id);
        if ($uc === null) {
            return $this->json(['message' => 'Collection entry not found'], Response::HTTP_NOT_FOUND);
        }
        if ($uc->getUser() !== $security->getUser()) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($uc);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function formatEntry(UserCollection $uc): array
    {
        $film = $uc->getFilm();
        return [
            'id'          => $uc->getId(),
            'status'      => $uc->getStatus(),
            'is_favorite' => $uc->isFavorite(),
            'film'        => [
                'tmdb_id'    => $film->getTmdbId(),
                'title'      => $film->getTitle(),
                'poster_path' => $film->getPosterPath(),
            ],
        ];
    }

    private function formatFullEntry(UserCollection $uc): array
    {
        $film = $uc->getFilm();
        return [
            'id'          => $uc->getId(),
            'status'      => $uc->getStatus(),
            'is_favorite' => $uc->isFavorite(),
            'rating'      => $uc->getRating(),
            'added_at'    => $uc->getAddedAt()?->format(\DateTimeInterface::ATOM),
            'watched_at'  => $uc->getWatchedAt()?->format(\DateTimeInterface::ATOM),
            'film'        => [
                'id'             => $film->getId(),
                'tmdb_id'        => $film->getTmdbId(),
                'title'          => $film->getTitle(),
                'original_title' => $film->getOriginalTitle(),
                'poster_path'    => $film->getPosterPath(),
                'backdrop_path'  => $film->getBackdropPath(),
                'release_date'   => $film->getReleaseDate()?->format('Y-m-d'),
                'runtime'        => $film->getRuntime(),
                'vote_average'   => $film->getVoteAverage(),
                'genres'         => array_map(
                    fn($g) => ['id' => $g->getId(), 'name' => $g->getName()],
                    $film->getGenres()->toArray()
                ),
            ],
        ];
    }
}
