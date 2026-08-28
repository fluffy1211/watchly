<?php

namespace App\Tests\Service;

use App\Entity\CommentReport;
use App\Entity\ListComment;
use App\Entity\Report;
use App\Entity\Review;
use App\Entity\ReviewReport;
use App\Entity\User;
use App\Enum\ReportAction;
use App\Exception\DuplicateReportException;
use App\Exception\InvalidReportReasonException;
use App\Exception\SelfReportException;
use App\Repository\ReportRepository;
use App\Service\ReportService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ReportServiceTest extends TestCase
{
    private ReportRepository&Stub $reports;
    private EntityManagerInterface&MockObject $em;
    private ReportService $service;

    protected function setUp(): void
    {
        $this->reports = $this->createStub(ReportRepository::class);
        $this->em      = $this->createMock(EntityManagerInterface::class);
        $this->service = new ReportService($this->reports, $this->em);
    }

    private function comment(User $author): ListComment
    {
        $comment = new ListComment();
        $comment->setAuthor($author);
        return $comment;
    }

    public function testFileRejectsSelfReport(): void
    {
        $author = new User();

        $this->em->expects($this->never())->method('persist');
        $this->expectException(SelfReportException::class);

        $this->service->file($author, $this->comment($author), 'Spam');
    }

    public function testFileRejectsDuplicate(): void
    {
        $author   = new User();
        $reporter = new User();
        $comment  = $this->comment($author);

        $this->reports->method('findOneByTargetAndReporter')
            ->willReturn($this->createStub(Report::class));

        $this->em->expects($this->never())->method('persist');
        $this->expectException(DuplicateReportException::class);

        $this->service->file($reporter, $comment, 'Spam');
    }

    public function testFileRejectsOverlongReason(): void
    {
        $this->reports->method('findOneByTargetAndReporter')->willReturn(null);

        $this->expectException(InvalidReportReasonException::class);

        $this->service->file(new User(), $this->comment(new User()), str_repeat('x', 501));
    }

    public function testFileBuildsCommentReportWithTrimmedReason(): void
    {
        $reporter = new User();
        $comment  = $this->comment(new User());

        $this->reports->method('findOneByTargetAndReporter')->willReturn(null);
        $this->em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(CommentReport::class));
        $this->em->expects($this->once())->method('flush');

        $report = $this->service->file($reporter, $comment, '  Spam  ');

        $this->assertInstanceOf(CommentReport::class, $report);
        $this->assertSame($comment, $report->getTarget());
        $this->assertSame($reporter, $report->getReporter());
        $this->assertSame('Spam', $report->getReason());
        $this->assertSame('comment', $report->getType());
    }

    public function testFileBuildsReviewReportForReviewTarget(): void
    {
        $review = new Review();
        $review->setUser(new User());

        $this->reports->method('findOneByTargetAndReporter')->willReturn(null);

        $report = $this->service->file(new User(), $review, null);

        $this->assertInstanceOf(ReviewReport::class, $report);
        $this->assertSame($review, $report->getTarget());
        $this->assertNull($report->getReason());
        $this->assertSame('review', $report->getType());
    }

    public function testFileTreatsBlankReasonAsNull(): void
    {
        $this->reports->method('findOneByTargetAndReporter')->willReturn(null);

        $report = $this->service->file(new User(), $this->comment(new User()), '   ');

        $this->assertNull($report->getReason());
    }

    public function testResolveKeepRemovesReportOnly(): void
    {
        $comment = $this->comment(new User());
        $report  = (new CommentReport())->setComment($comment);

        $this->em->expects($this->once())->method('remove')->with($report);
        $this->em->expects($this->once())->method('flush');

        $this->service->resolve($report, ReportAction::Keep);
    }

    public function testResolveDeleteRemovesTarget(): void
    {
        $comment = $this->comment(new User());
        $report  = (new CommentReport())->setComment($comment);

        $this->em->expects($this->once())->method('remove')->with($comment);
        $this->em->expects($this->once())->method('flush');

        $this->service->resolve($report, ReportAction::Delete);
    }
}
