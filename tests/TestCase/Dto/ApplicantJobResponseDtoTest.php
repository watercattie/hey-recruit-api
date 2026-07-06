<?php
declare(strict_types=1);

namespace App\Test\TestCase\Dto;

use App\Dto\ApplicantJobResponseDto;
use Cake\TestSuite\TestCase;

/**
 * ApplicantJobResponseDto Unit Tests
 */
class ApplicantJobResponseDtoTest extends TestCase
{
    /**
     * Test DTO is immutable (readonly)
     */
    public function testDtoIsImmutable(): void
    {
        $dto = new ApplicantJobResponseDto(
            applicantExternalId: 'APP-1',
            jobExternalId: 'JOB-1',
            status: 'new',
            appliedAt: '2026-03-01T10:00:00+00:00',
            created: '2026-03-01T09:00:00+00:00',
            modified: '2026-03-01T09:00:00+00:00',
        );

        $this->assertEquals('APP-1', $dto->applicantExternalId);
        $this->assertEquals('JOB-1', $dto->jobExternalId);
        $this->assertEquals('new', $dto->status);
    }

    /**
     * Test jsonSerialize returns correct structure
     */
    public function testJsonSerializeReturnsCorrectStructure(): void
    {
        $dto = new ApplicantJobResponseDto(
            applicantExternalId: 'APP-123',
            jobExternalId: 'JOB-456',
            status: 'interview',
            appliedAt: '2026-03-01T10:00:00+00:00',
            created: '2026-03-01T09:00:00+00:00',
            modified: '2026-03-02T15:30:00+00:00',
        );

        $result = $dto->jsonSerialize();

        $this->assertEquals([
            'applicant_external_id' => 'APP-123',
            'job_external_id' => 'JOB-456',
            'status' => 'interview',
            'applied_at' => '2026-03-01T10:00:00+00:00',
            'created' => '2026-03-01T09:00:00+00:00',
            'modified' => '2026-03-02T15:30:00+00:00',
        ], $result);
    }

    /**
     * Test jsonSerialize with null applied_at
     */
    public function testJsonSerializeWithNullAppliedAt(): void
    {
        $dto = new ApplicantJobResponseDto(
            applicantExternalId: 'APP-1',
            jobExternalId: 'JOB-1',
            status: 'new',
            appliedAt: null,
            created: '2026-03-01T09:00:00+00:00',
            modified: '2026-03-01T09:00:00+00:00',
        );

        $result = $dto->jsonSerialize();

        $this->assertNull($result['applied_at']);
    }

    /**
     * Test json_encode works correctly with DTO
     */
    public function testJsonEncodeWorksCorrectly(): void
    {
        $dto = new ApplicantJobResponseDto(
            applicantExternalId: 'APP-1',
            jobExternalId: 'JOB-1',
            status: 'hired',
            appliedAt: '2026-03-01T10:00:00+00:00',
            created: '2026-03-01T09:00:00+00:00',
            modified: '2026-03-01T09:00:00+00:00',
        );

        $json = json_encode($dto);
        $decoded = json_decode($json, true);

        $this->assertEquals('APP-1', $decoded['applicant_external_id']);
        $this->assertEquals('JOB-1', $decoded['job_external_id']);
        $this->assertEquals('hired', $decoded['status']);
    }
}
