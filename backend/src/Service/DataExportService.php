<?php

namespace App\Service;

use App\Entity\Film;
use App\Entity\User;
use App\Repository\ListCommentRepository;
use App\Repository\MovieListRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserCollectionRepository;

/**
 * Assembles every piece of personal data held about a user, so it can be
 * handed back in a reusable format (RGPD art. 20, droit à la portabilité).
 */
class DataExportService
{
    public function __construct(
        private UserCollectionRepository $collectionRepo,
        private ReviewRepository $reviewRepo,
        private MovieListRepository $listRepo,
        private ListCommentRepository $commentRepo,
    ) {}

    public function export(User $user): array
    {
        return [
            'export_genere_le' => (new \DateTimeImmutable())->format('c'),
            'profil' => [
                'email' => $user->getEmail(),
                'nom_utilisateur' => $user->getUsername(),
                'biographie' => $user->getBio(),
                'avatar' => $user->getAvatarPath(),
                'roles' => $user->getRoles(),
                'consentement_le' => $user->getConsentedAt()?->format('c'),
                'inscrit_le' => $user->getCreatedAt()?->format('c'),
                'modifie_le' => $user->getUpdatedAt()?->format('c'),
            ],
            'collection' => array_map(fn ($uc) => [
                'film' => $this->film($uc->getFilm()),
                'statut' => $uc->getStatus(),
                'favori' => $uc->isFavorite(),
                'note' => $uc->getRating(),
                'ajoute_le' => $uc->getAddedAt()?->format('c'),
                'vu_le' => $uc->getWatchedAt()?->format('c'),
            ], $this->collectionRepo->findBy(['user' => $user])),
            'avis' => array_map(fn ($review) => [
                'film' => $this->film($review->getFilm()),
                'contenu' => $review->getContent(),
                'cree_le' => $review->getCreatedAt()?->format('c'),
                'modifie_le' => $review->getUpdatedAt()?->format('c'),
            ], $this->reviewRepo->findBy(['user' => $user])),
            'listes' => array_map(fn ($list) => [
                'titre' => $list->getTitle(),
                'description' => $list->getDescription(),
                'visibilite' => $list->getVisibility(),
                'cree_le' => $list->getCreatedAt()?->format('c'),
                'films' => array_map(
                    fn ($lf) => $this->film($lf->getFilm()),
                    $list->getListFilms()->toArray()
                ),
            ], $this->listRepo->findBy(['owner' => $user])),
            'commentaires' => array_map(fn ($comment) => [
                'liste' => $comment->getList()?->getTitle(),
                'contenu' => $comment->getContent(),
                'cree_le' => $comment->getCreatedAt()?->format('c'),
                'modifie_le' => $comment->getUpdatedAt()?->format('c'),
            ], $this->commentRepo->findBy(['author' => $user])),
        ];
    }

    private function film(?Film $film): ?array
    {
        if (!$film) {
            return null;
        }

        return [
            'tmdb_id' => $film->getTmdbId(),
            'titre' => $film->getTitle(),
        ];
    }
}
