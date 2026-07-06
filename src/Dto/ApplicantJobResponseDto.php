<?php
declare(strict_types=1);

namespace App\Dto;

use JsonSerializable;

readonly class ApplicantJobResponseDto implements JsonSerializable
{
    public function __construct(
        public string $applicantExternalId,
        public string $jobExternalId,
        public string $status,
        public ?string $appliedAt,
        public string $created,
        public string $modified,
    ) {
    }

    /** @inheritDoc */
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
