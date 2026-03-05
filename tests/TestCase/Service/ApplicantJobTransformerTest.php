<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\Applicant;
use App\Model\Entity\ApplicantJob;
use App\Model\Entity\Job;
use App\Service\ApplicantJobTransformer;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;

/**
 * ApplicantJobTransformer Unit Tests
 */
class ApplicantJobTransformerTest extends TestCase
{
    private ApplicantJobTransformer $transformer;

    public function setUp(): void
    {
        parent::setUp();
        $this->transformer = new ApplicantJobTransformer();
    }

    /**
     * Test transform maps entity to DTO correctly
     */
    public function testTransformMapsEntityToDto(): void
    {
        $entity = $this->createApplicantJob(
            applicantExternalId: 'APP-123',
            jobExternalId: 'JOB-456',
            status: 'interview',
            appliedAt: new DateTime('2026-03-01 10:00:00'),
            created: new DateTime('2026-03-01 09:00:00'),
            modified: new DateTime('2026-03-02 15:30:00'),
        );

        $dto = $this->transformer->transform($entity);

        $this->assertEquals('APP-123', $dto->applicantExternalId);
        $this->assertEquals('JOB-456', $dto->jobExternalId);
        $this->assertEquals('interview', $dto->status);
        $this->assertStringContainsString('2026-03-01', $dto->appliedAt);
        $this->assertStringContainsString('2026-03-01', $dto->created);
        $this->assertStringContainsString('2026-03-02', $dto->modified);
    }

    /**
     * Test transform handles null applied_at
     */
    public function testTransformHandlesNullAppliedAt(): void
    {
        $entity = $this->createApplicantJob(
            applicantExternalId: 'APP-123',
            jobExternalId: 'JOB-456',
            status: 'new',
            appliedAt: null,
            created: new DateTime('2026-03-01 09:00:00'),
            modified: new DateTime('2026-03-01 09:00:00'),
        );

        $dto = $this->transformer->transform($entity);

        $this->assertNull($dto->appliedAt);
    }

    /**
     * Test transformAll with multiple entities
     */
    public function testTransformAllWithMultipleEntities(): void
    {
        $entities = [
            $this->createApplicantJob('APP-1', 'JOB-1', 'new'),
            $this->createApplicantJob('APP-2', 'JOB-2', 'hired'),
            $this->createApplicantJob('APP-3', 'JOB-3', 'rejected'),
        ];

        $dtos = $this->transformer->transformAll($entities);

        $this->assertCount(3, $dtos);
        $this->assertEquals('APP-1', $dtos[0]->applicantExternalId);
        $this->assertEquals('APP-2', $dtos[1]->applicantExternalId);
        $this->assertEquals('APP-3', $dtos[2]->applicantExternalId);
    }

    /**
     * Test transformAll with empty collection
     */
    public function testTransformAllWithEmptyCollection(): void
    {
        $dtos = $this->transformer->transformAll([]);

        $this->assertEmpty($dtos);
    }

    /**
     * Create ApplicantJob entity for testing
     */
    private function createApplicantJob(
        string $applicantExternalId,
        string $jobExternalId,
        string $status,
        ?DateTime $appliedAt = null,
        ?DateTime $created = null,
        ?DateTime $modified = null,
    ): ApplicantJob {
        $applicant = new Applicant();
        $applicant->external_id = $applicantExternalId;

        $job = new Job();
        $job->external_id = $jobExternalId;

        $entity = new ApplicantJob();
        $entity->status = $status;
        $entity->applied_at = $appliedAt;
        $entity->created = $created ?? new DateTime();
        $entity->modified = $modified ?? new DateTime();
        $entity->applicant = $applicant;
        $entity->job = $job;

        return $entity;
    }
}
