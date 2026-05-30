<?php

namespace App\Tests\Service;

use App\Entity\UserCollection;
use App\Service\CollectionService;
use PHPUnit\Framework\TestCase;

class CollectionServiceTest extends TestCase
{
    private CollectionService $service;

    protected function setUp(): void
    {
        $this->service = new CollectionService();
    }

    private function makeUc(string $status = UserCollection::STATUS_WATCHLIST): UserCollection
    {
        $uc = new UserCollection();
        $uc->setStatus($status);
        return $uc;
    }

    public function testSetStatusToWatched(): void
    {
        $uc = $this->makeUc();
        $this->service->setStatus($uc, UserCollection::STATUS_WATCHED);
        $this->assertSame(UserCollection::STATUS_WATCHED, $uc->getStatus());
        $this->assertNotNull($uc->getWatchedAt());
    }

    public function testSetStatusToWatchlistResetsFields(): void
    {
        $uc = $this->makeUc(UserCollection::STATUS_WATCHED);
        $uc->setIsFavorite(true);
        $uc->setRating(4);
        $uc->setWatchedAt(new \DateTimeImmutable());

        $this->service->setStatus($uc, UserCollection::STATUS_WATCHLIST);

        $this->assertSame(UserCollection::STATUS_WATCHLIST, $uc->getStatus());
        $this->assertFalse($uc->isFavorite());
        $this->assertNull($uc->getRating());
        $this->assertNull($uc->getWatchedAt());
    }

    public function testSetStatusInvalidThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->setStatus($this->makeUc(), 'INVALID');
    }

    public function testSetFavoriteTrueOnWatched(): void
    {
        $uc = $this->makeUc(UserCollection::STATUS_WATCHED);
        $this->service->setFavorite($uc, true);
        $this->assertTrue($uc->isFavorite());
    }

    public function testSetFavoriteTrueOnWatchlistThrows(): void
    {
        $this->expectException(\LogicException::class);
        $this->service->setFavorite($this->makeUc(), true);
    }

    public function testSetFavoriteFalseAlwaysWorks(): void
    {
        $uc = $this->makeUc(UserCollection::STATUS_WATCHED);
        $uc->setIsFavorite(true);
        $this->service->setFavorite($uc, false);
        $this->assertFalse($uc->isFavorite());
    }

    public function testSetRatingOnWatched(): void
    {
        $uc = $this->makeUc(UserCollection::STATUS_WATCHED);
        $this->service->setRating($uc, 3);
        $this->assertSame(3, $uc->getRating());
    }

    public function testSetRatingNullOnWatched(): void
    {
        $uc = $this->makeUc(UserCollection::STATUS_WATCHED);
        $uc->setRating(4);
        $this->service->setRating($uc, null);
        $this->assertNull($uc->getRating());
    }

    public function testSetRatingOnWatchlistThrows(): void
    {
        $this->expectException(\LogicException::class);
        $this->service->setRating($this->makeUc(), 3);
    }

    public function testSetRatingOutOfRangeThrows(): void
    {
        $uc = $this->makeUc(UserCollection::STATUS_WATCHED);
        $this->expectException(\InvalidArgumentException::class);
        $this->service->setRating($uc, 6);
    }

    public function testSetRatingZeroThrows(): void
    {
        $uc = $this->makeUc(UserCollection::STATUS_WATCHED);
        $this->expectException(\InvalidArgumentException::class);
        $this->service->setRating($uc, 0);
    }
}
