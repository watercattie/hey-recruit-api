<?php
declare(strict_types=1);

namespace App\Dto;

use App\Enum\ApplicationStatus;
use Cake\I18n\DateTime;

/**
 * DTO for POST /api/v2/applicant-jobs upsert request.
 *
 * Pure data mapping - no validation logic.
 * Validation is handled by RequestValidator (schema) and BusinessValidator (business rules).
 */
readonly class ApplicantJobUpsertRequestDto
{
    /**
     * Constructor.
     *
     * @param \App\Dto\ApplicantRequestDto $applicant Applicant data.
     * @param int $jobId The job ID.
     * @param string $status The application status.
     * @param \Cake\I18n\DateTime $appliedAt When the application was submitted.
     */
    public function __construct(
        public ApplicantRequestDto $applicant,
        public int $jobId,
        public string $status,
        public DateTime $appliedAt,
    ) {
    }

    /**
     * Create from request array.
     *
     * Pure mapping with defaults - does NOT validate.
     * Call RequestValidator::validateApplicantJobUpsert() before this.
     *
     * @param array<string, mixed> $data The request data.
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $applicantData = $data['applicant'] ?? [];
        if (!is_array($applicantData)) {
            $applicantData = [];
        }

        $status = (string)($data['status'] ?? ApplicationStatus::New->value);

        $appliedAt = isset($data['applied_at'])
            ? new DateTime($data['applied_at'])
            : new DateTime();

        return new self(
            applicant: ApplicantRequestDto::fromArray($applicantData),
            jobId: (int)($data['job_id'] ?? 0),
            status: $status,
            appliedAt: $appliedAt,
        );
    }
}
