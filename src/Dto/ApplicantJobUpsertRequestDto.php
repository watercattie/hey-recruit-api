<?php
declare(strict_types=1);

namespace App\Dto;

use Cake\I18n\DateTime;

readonly class ApplicantJobUpsertRequestDto
{
    public function __construct(
        public ApplicantRequestDto $applicant,
        public int $jobId,
        public ?string $status,
        public DateTime $appliedAt,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $applicantData = $data['applicant'] ?? [];
        if (!is_array($applicantData)) {
            $applicantData = [];
        }

        $status = isset($data['status']) ? (string)$data['status'] : null;

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
