<?php

namespace App\Controller;

use App\Entity\ListFilm;
use App\Entity\MovieList;
use App\Repository\FilmRepository;
use App\Repository\ListFilmRepository;
use App\Repository\MovieListRepository;
use App\Service\FilmService;
use App\Service\ListService;
use App\Service\TMDBService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ListController extends AbstractController
{
    #[Route('/api/lists', name: 'api_lists_create', methods: ['POST'])]
    public function create(
        Request $request,
        ListService $service,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        $list = new MovieList();
        $list->setOwner($security->getUser());

        try {
            $service->setTitle($list, (string) ($data['title'] ?? ''));
            $service->setVisibility($list, $data['visibility'] ?? MovieList::VISIBILITY_PUBLIC);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $list->setDescription($data['description'] ?? null);

        $em->persist($list);
        $em->flush();

        return $this->json(['message' => 'List created', 'list' => $this->formatFullList($list)], Response::HTTP_CREATED);
    }

    #[Route('/api/lists', name: 'api_lists_browse', methods: ['GET'])]
    public function browse(Request $request, MovieListRepository $repo): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));
        $lists = $repo->searchPublic($query !== '' ? $query : null);

        return $this->json(array_map(fn($l) => $this->formatListSummary($l), $lists));
    }

    #[Route('/api/lists/{id}', name: 'api_lists_show', methods: ['GET'])]
    public function show(int $id, MovieListRepository $repo, ListService $service, Security $security): JsonResponse
    {
        $list = $repo->find($id);
        if ($list === null) {
            return $this->json(['message' => 'List not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$service->canView($list, $security->getUser())) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        return $this->json($this->formatFullList($list));
    }

    #[Route('/api/lists/{id}', name: 'api_lists_update', methods: ['PATCH'])]
    public function update(
        int $id,
        Request $request,
        MovieListRepository $repo,
        ListService $service,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $list = $repo->find($id);
        if ($list === null) {
            return $this->json(['message' => 'List not found'], Response::HTTP_NOT_FOUND);
        }
        if ($list->getOwner() !== $security->getUser()) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        try {
            if (array_key_exists('title', $data)) {
                $service->setTitle($list, (string) $data['title']);
            }
            if (array_key_exists('visibility', $data)) {
                $service->setVisibility($list, $data['visibility']);
            }
        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (array_key_exists('description', $data)) {
            $list->setDescription($data['description']);
        }

        $em->flush();

        return $this->json(['message' => 'List updated', 'list' => $this->formatFullList($list)]);
    }

    #[Route('/api/lists/{id}', name: 'api_lists_delete', methods: ['DELETE'])]
    public function delete(int $id, MovieListRepository $repo, EntityManagerInterface $em, Security $security): JsonResponse
    {
        $list = $repo->find($id);
        if ($list === null) {
            return $this->json(['message' => 'List not found'], Response::HTTP_NOT_FOUND);
        }
        if ($list->getOwner() !== $security->getUser()) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($list);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/lists/{id}/films/{tmdbId}', name: 'api_lists_add_film', methods: ['POST'])]
    public function addFilm(
        int $id,
        int $tmdbId,
        MovieListRepository $listRepo,
        FilmRepository $filmRepo,
        ListFilmRepository $listFilmRepo,
        TMDBService $tmdb,
        FilmService $filmService,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $list = $listRepo->find($id);
        if ($list === null) {
            return $this->json(['message' => 'List not found'], Response::HTTP_NOT_FOUND);
        }
        if ($list->getOwner() !== $security->getUser()) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $film = $filmRepo->findOneBy(['tmdbId' => $tmdbId]);
        if ($film === null) {
            $tmdbData = $tmdb->getMovieById($tmdbId);
            if (empty($tmdbData)) {
                return $this->json(['message' => 'Film not found on TMDB'], Response::HTTP_NOT_FOUND);
            }
            $film = $filmService->findOrCreate($tmdbData, $em);
            $filmService->syncGenres($film, $tmdbData['genres'] ?? [], $em);
        }

        if ($listFilmRepo->findOneBy(['list' => $list, 'film' => $film]) !== null) {
            return $this->json(['message' => 'Film already in list'], Response::HTTP_CONFLICT);
        }

        $listFilm = new ListFilm();
        $listFilm->setList($list);
        $listFilm->setFilm($film);

        $em->persist($listFilm);
        $em->flush();

        return $this->json(['message' => 'Film added to list'], Response::HTTP_CREATED);
    }

    #[Route('/api/lists/{id}/films/{tmdbId}', name: 'api_lists_remove_film', methods: ['DELETE'])]
    public function removeFilm(
        int $id,
        int $tmdbId,
        MovieListRepository $listRepo,
        FilmRepository $filmRepo,
        ListFilmRepository $listFilmRepo,
        EntityManagerInterface $em,
        Security $security,
    ): JsonResponse {
        $list = $listRepo->find($id);
        if ($list === null) {
            return $this->json(['message' => 'List not found'], Response::HTTP_NOT_FOUND);
        }
        if ($list->getOwner() !== $security->getUser()) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $film = $filmRepo->findOneBy(['tmdbId' => $tmdbId]);
        $listFilm = $film !== null ? $listFilmRepo->findOneBy(['list' => $list, 'film' => $film]) : null;
        if ($listFilm === null) {
            return $this->json(['message' => 'Film not in list'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($listFilm);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function formatListSummary(MovieList $list): array
    {
        return [
            'id'          => $list->getId(),
            'title'       => $list->getTitle(),
            'description' => $list->getDescription(),
            'visibility'  => $list->getVisibility(),
            'created_at'  => $list->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'film_count'  => $list->getListFilms()->count(),
            'owner'       => [
                'username' => $list->getOwner()->getUsername(),
            ],
        ];
    }

    private function formatFullList(MovieList $list): array
    {
        $films = array_map(fn(ListFilm $lf) => [
            'tmdb_id'     => $lf->getFilm()->getTmdbId(),
            'title'       => $lf->getFilm()->getTitle(),
            'poster_path' => $lf->getFilm()->getPosterPath(),
            'added_at'    => $lf->getAddedAt()?->format(\DateTimeInterface::ATOM),
        ], $list->getListFilms()->toArray());

        return [
            'id'          => $list->getId(),
            'title'       => $list->getTitle(),
            'description' => $list->getDescription(),
            'visibility'  => $list->getVisibility(),
            'created_at'  => $list->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updated_at'  => $list->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            'owner'       => [
                'username' => $list->getOwner()->getUsername(),
            ],
            'films'       => $films,
        ];
    }
}
