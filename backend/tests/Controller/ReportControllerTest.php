<?php

namespace App\Tests\Controller;

use App\Entity\CommentReport;
use App\Entity\Film;
use App\Entity\ListComment;
use App\Entity\MovieList;
use App\Entity\Report;
use App\Entity\Review;
use App\Entity\ReviewReport;
use App\Entity\User;
use App\Tests\BaseWebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ReportControllerTest extends BaseWebTestCase
{
    private function createUser(string $email, string $username, array $roles = ['ROLE_USER']): User
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user   = new User();
        $user->setEmail($email)->setUsername($username)->setRoles($roles);
        $user->setPassword($hasher->hashPassword($user, 'password123'));
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    private function tokenFor(User $user): string
    {
        return static::getContainer()->get(JWTTokenManagerInterface::class)->create($user);
    }

    private function authHeaders(string $token): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'];
    }

    /** Seed a reported comment in one flush; returns [reportId, commentId]. */
    private function seedReportedComment(User $author, User $reporter, string $listTitle = 'Favourites', ?string $reason = 'Spam'): array
    {
        $list = new MovieList();
        $list->setOwner($author)->setTitle($listTitle)->setVisibility('PUBLIC');
        $this->em->persist($list);

        $comment = new ListComment();
        $comment->setList($list)->setAuthor($author)->setContent('a reported comment');
        $this->em->persist($comment);

        $report = (new CommentReport())->setComment($comment)->setReporter($reporter)->setReason($reason);
        $this->em->persist($report);
        $this->em->flush();

        return [$report->getId(), $comment->getId()];
    }

    /** Seed a reported review in one flush; returns [reportId, reviewId]. */
    private function seedReportedReview(User $author, User $reporter, string $filmTitle = 'Inception', ?string $reason = 'Spam'): array
    {
        $film = new Film();
        $film->setTmdbId(random_int(1, 9_999_999))->setTitle($filmTitle);
        $this->em->persist($film);

        $review = new Review();
        $review->setUser($author)->setFilm($film)->setContent('a reported review');
        $this->em->persist($review);

        $report = (new ReviewReport())->setReview($review)->setReporter($reporter)->setReason($reason);
        $this->em->persist($report);
        $this->em->flush();

        return [$report->getId(), $review->getId()];
    }

    private function seedReview(User $author): int
    {
        $film = new Film();
        $film->setTmdbId(random_int(1, 9_999_999))->setTitle('Dune');
        $this->em->persist($film);

        $review = new Review();
        $review->setUser($author)->setFilm($film)->setContent('a review to report');
        $this->em->persist($review);
        $this->em->flush();

        return $review->getId();
    }

    // --- Admin moderation queue ----------------------------------------------

    public function testQueueRequiresAdmin(): void
    {
        $user = $this->createUser('user@test.com', 'user');

        $this->client->request('GET', '/api/admin/reports', [], [], $this->authHeaders($this->tokenFor($user)));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testQueueListsBothTargetTypes(): void
    {
        $author   = $this->createUser('author@test.com', 'author');
        $reporter = $this->createUser('reporter@test.com', 'reporter');
        $admin    = $this->createUser('admin@test.com', 'admin', ['ROLE_USER', 'ROLE_ADMIN']);
        $adminToken = $this->tokenFor($admin);

        $this->seedReportedComment($author, $reporter, 'My List');
        $this->seedReportedReview($author, $reporter, 'Dune', null);

        $this->client->request('GET', '/api/admin/reports', [], [], $this->authHeaders($adminToken));

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $data);

        $byType = [];
        foreach ($data as $row) {
            $byType[$row['type']] = $row;
        }

        $this->assertArrayHasKey('comment', $byType);
        $this->assertArrayHasKey('review', $byType);
        $this->assertSame('My List', $byType['comment']['target']['context']);
        $this->assertSame('Dune', $byType['review']['target']['context']);
        $this->assertSame('author', $byType['comment']['target']['author']['username']);
        $this->assertSame('reporter', $byType['comment']['reporter']['username']);
        $this->assertSame('Spam', $byType['comment']['reason']);
        $this->assertNull($byType['review']['reason']);
    }

    // --- Resolve -----------------------------------------------------------------

    public function testResolveKeepDismissesReportKeepsContent(): void
    {
        $author   = $this->createUser('author@test.com', 'author');
        $reporter = $this->createUser('reporter@test.com', 'reporter');
        $admin    = $this->createUser('admin@test.com', 'admin', ['ROLE_USER', 'ROLE_ADMIN']);
        $adminToken = $this->tokenFor($admin);

        [$reportId, $commentId] = $this->seedReportedComment($author, $reporter);

        $this->client->request('PATCH', '/api/admin/reports/' . $reportId, [], [],
            $this->authHeaders($adminToken), json_encode(['action' => 'keep']));

        $this->assertResponseStatusCodeSame(200);
        $this->em->clear();
        $this->assertNull($this->em->getRepository(Report::class)->find($reportId));
        $this->assertNotNull($this->em->getRepository(ListComment::class)->find($commentId));
    }

    public function testResolveDeleteRemovesContentAndReport(): void
    {
        $author   = $this->createUser('author@test.com', 'author');
        $reporter = $this->createUser('reporter@test.com', 'reporter');
        $admin    = $this->createUser('admin@test.com', 'admin', ['ROLE_USER', 'ROLE_ADMIN']);
        $adminToken = $this->tokenFor($admin);

        [$reportId, $reviewId] = $this->seedReportedReview($author, $reporter);

        $this->client->request('PATCH', '/api/admin/reports/' . $reportId, [], [],
            $this->authHeaders($adminToken), json_encode(['action' => 'delete']));

        $this->assertResponseStatusCodeSame(200);
        $this->em->clear();
        $this->assertNull($this->em->getRepository(Report::class)->find($reportId));
        $this->assertNull($this->em->getRepository(Review::class)->find($reviewId));
    }

    public function testResolveUnknownReportReturns404(): void
    {
        $admin = $this->createUser('admin@test.com', 'admin', ['ROLE_USER', 'ROLE_ADMIN']);

        $this->client->request('PATCH', '/api/admin/reports/999999', [], [],
            $this->authHeaders($this->tokenFor($admin)), json_encode(['action' => 'keep']));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testResolveRejectsUnknownAction(): void
    {
        $author   = $this->createUser('author@test.com', 'author');
        $reporter = $this->createUser('reporter@test.com', 'reporter');
        $admin    = $this->createUser('admin@test.com', 'admin', ['ROLE_USER', 'ROLE_ADMIN']);
        $adminToken = $this->tokenFor($admin);

        [$reportId] = $this->seedReportedComment($author, $reporter);

        $this->client->request('PATCH', '/api/admin/reports/' . $reportId, [], [],
            $this->authHeaders($adminToken), json_encode(['action' => 'burn']));

        $this->assertResponseStatusCodeSame(400);
    }

    // --- Filing a review report (previously uncovered) -------------------------

    public function testReportReviewHappyPath(): void
    {
        $author   = $this->createUser('author@test.com', 'author');
        $reporter = $this->createUser('reporter@test.com', 'reporter');
        $reviewId = $this->seedReview($author);

        $this->client->request('POST', '/api/reviews/' . $reviewId . '/report', [], [],
            $this->authHeaders($this->tokenFor($reporter)), json_encode(['reason' => 'Offensive']));

        $this->assertResponseStatusCodeSame(201);
        $this->em->clear();
        $this->assertCount(1, $this->em->getRepository(ReviewReport::class)->findAll());
    }

    public function testCannotReportOwnReview(): void
    {
        $author   = $this->createUser('author@test.com', 'author');
        $reviewId = $this->seedReview($author);

        $this->client->request('POST', '/api/reviews/' . $reviewId . '/report', [], [],
            $this->authHeaders($this->tokenFor($author)), json_encode(['reason' => 'x']));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCannotReportReviewTwice(): void
    {
        $author   = $this->createUser('author@test.com', 'author');
        $reporter = $this->createUser('reporter@test.com', 'reporter');
        $reviewId = $this->seedReview($author);
        $headers  = $this->authHeaders($this->tokenFor($reporter));

        $this->client->request('POST', '/api/reviews/' . $reviewId . '/report', [], [], $headers,
            json_encode(['reason' => 'Spam']));
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('POST', '/api/reviews/' . $reviewId . '/report', [], [], $headers,
            json_encode(['reason' => 'Spam again']));
        $this->assertResponseStatusCodeSame(409);
    }

    public function testReportUnknownReviewReturns404(): void
    {
        $reporter = $this->createUser('reporter@test.com', 'reporter');

        $this->client->request('POST', '/api/reviews/999999/report', [], [],
            $this->authHeaders($this->tokenFor($reporter)), json_encode(['reason' => 'x']));

        $this->assertResponseStatusCodeSame(404);
    }
}
