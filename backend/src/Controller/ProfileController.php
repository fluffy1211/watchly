<?php

namespace App\Controller;

use App\Repository\MovieListRepository;
use App\Repository\UserCollectionRepository;
use App\Repository\UserRepository;
use App\Service\ProfileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProfileController extends AbstractController
{
    #[Route('/api/profile/{username}', name: 'api_profile_show', methods: ['GET'])]
    public function show(
        string $username,
        UserRepository $userRepo,
        UserCollectionRepository $collectionRepo,
        MovieListRepository $listRepo,
        Security $security,
    ): JsonResponse {
        $user = $userRepo->findOneBy(['username' => $username]);

        if (!$user) {
            return $this->json(['message' => 'Utilisateur introuvable'], Response::HTTP_NOT_FOUND);
        }

        $isOwner = $security->getUser() === $user;
        $lists = $isOwner
            ? $listRepo->findAllByOwnerUsername($username)
            : $listRepo->findPublicByOwnerUsername($username);

        $watched = $collectionRepo->findWatchedByUser($user);

        $ratings = array_filter(array_map(fn ($uc) => $uc->getRating(), $watched), fn ($r) => $r !== null);
        $avgRating = count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 1) : null;
        $favorites = count(array_filter($watched, fn ($uc) => $uc->isFavorite()));

        $films = array_map(fn ($uc) => [
            'tmdb_id' => $uc->getFilm()->getTmdbId(),
            'title' => $uc->getFilm()->getTitle(),
            'poster_path' => $uc->getFilm()->getPosterPath(),
            'release_date' => $uc->getFilm()->getReleaseDate()?->format('Y-m-d'),
            'rating' => $uc->getRating(),
            'watched_at' => $uc->getWatchedAt()?->format('c'),
        ], $watched);

        return $this->json([
            'username' => $user->getUsername(),
            'bio' => $user->getBio(),
            'avatar_url' => $user->getAvatarPath() ? '/' . $user->getAvatarPath() : null,
            'member_since' => $user->getCreatedAt()->format('Y-m-d'),
            'stats' => [
                'watched' => count($watched),
                'favorites' => $favorites,
                'average_rating' => $avgRating,
            ],
            'watched_films' => $films,
            'lists' => array_map(fn ($l) => [
                'id' => $l->getId(),
                'title' => $l->getTitle(),
                'description' => $l->getDescription(),
                'visibility' => $l->getVisibility(),
                'film_count' => $l->getListFilms()->count(),
            ], $lists),
        ]);
    }

    #[Route('/api/profile', name: 'api_profile_update', methods: ['PUT'])]
    public function updateBio(
        Request $request,
        Security $security,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
    ): JsonResponse {
        $user = $security->getUser();
        $data = json_decode($request->getContent(), true) ?? [];

        $constraints = new Assert\Collection([
            'bio' => [new Assert\Length(max: 500)],
        ]);

        $violations = $validator->validate($data, $constraints);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $v) {
                $errors[] = $v->getPropertyPath() . ': ' . $v->getMessage();
            }
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->setBio($data['bio'] ?? null);
        $em->flush();

        return $this->json(['message' => 'Profil mis à jour', 'bio' => $user->getBio()]);
    }

    #[Route('/api/profile/password', name: 'api_profile_change_password', methods: ['PUT'])]
    public function changePassword(
        Request $request,
        Security $security,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
    ): JsonResponse {
        $user = $security->getUser();
        $data = json_decode($request->getContent(), true) ?? [];

        $constraints = new Assert\Collection([
            'current_password' => [new Assert\NotBlank()],
            'new_password' => [new Assert\NotBlank(), new Assert\Length(min: 8)],
            'new_password_confirmation' => [new Assert\NotBlank()],
        ]);

        $violations = $validator->validate($data, $constraints);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($data['new_password'] !== $data['new_password_confirmation']) {
            return $this->json(['errors' => ['new_password_confirmation: Passwords do not match']], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$passwordHasher->isPasswordValid($user, $data['current_password'])) {
            return $this->json(['message' => 'Current password is incorrect'], Response::HTTP_FORBIDDEN);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $data['new_password']));
        $em->flush();

        return $this->json(['message' => 'Password updated successfully']);
    }

    #[Route('/api/profile', name: 'api_profile_delete', methods: ['DELETE'])]
    public function deleteAccount(
        Request $request,
        Security $security,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
    ): JsonResponse {
        $user = $security->getUser();
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['password'])) {
            return $this->json(['message' => 'Password is required'], Response::HTTP_BAD_REQUEST);
        }

        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['message' => 'Password is incorrect'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($user);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/profile/avatar', name: 'api_profile_avatar_upload', methods: ['POST'])]
    public function uploadAvatar(
        Request $request,
        Security $security,
        ProfileService $profileService,
        EntityManagerInterface $em,
    ): JsonResponse {
        $user = $security->getUser();
        $file = $request->files->get('avatar');

        if (!$file) {
            return $this->json(['message' => 'Aucun fichier envoyé'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $path = $profileService->processAvatar($file, $user->getId());
        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $user->setAvatarPath($path);
        $em->flush();

        return $this->json(['avatar_url' => '/' . $path]);
    }

    #[Route('/api/profile/avatar', name: 'api_profile_avatar_delete', methods: ['DELETE'])]
    public function deleteAvatar(
        Security $security,
        ProfileService $profileService,
        EntityManagerInterface $em,
    ): JsonResponse {
        $user = $security->getUser();

        $profileService->deleteAvatar($user->getId());
        $user->setAvatarPath(null);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
