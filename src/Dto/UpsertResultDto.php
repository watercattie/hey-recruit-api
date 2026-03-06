<?php
declare(strict_types=1);

namespace App\Dto;

use JsonSerializable;

readonly class UpsertResultDto implements JsonSerializable
{
    public const RESULT_CREATED = 'created';
    public const RESULT_UPDATED = 'updated';
    public const RESULT_NOOP = 'noop';

    public function __construct(
        public string $result,
        public int $applicantJobId,
        public int $applicantId,
    ) {
    }

    /** @inheritDoc */
    public function jsonSerialize(): array
    {
        return [
            'result' => $this->result,
            'applicant_job_id' => $this->applicantJobId,
            'applicant_id' => $this->applicantId,
        ];
    }
}
