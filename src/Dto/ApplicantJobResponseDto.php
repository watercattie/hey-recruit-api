<?php
declare(strict_types=1);

namespace App\Dto;

use JsonSerializable;

/**
 * DTO for ApplicantJob API response.
 *
 * Immutable data transfer object representing an applicant-job relationship
 * in the API response format.
 */
readonly class ApplicantJobResponseDto implements JsonSerializable
{
    /**
     * Constructor.
     *
     * @param string $applicantExternalId The external ID of the applicant.
     * @param string $jobExternalId The external ID of the job.
     * @param string $status The application status.
     * @param string|null $appliedAt ISO8601 timestamp of when applied.
     * @param string $created ISO8601 timestamp of creation.
     * @param string $modified ISO8601 timestamp of last modification.
     */
    public function __construct(
        public string $applicantExternalId,
        public string $jobExternalId,
        public string $status,
        public ?string $appliedAt,
        public string $created,
        public string $modified,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'applicant_external_id' => $this->applicantExternalId,
            'job_external_id' => $this->jobExternalId,
            'status' => $this->status,
            'applied_at' => $this->appliedAt,
            'created' => $this->created,
            'modified' => $this->modified,
        ];
    }
}
