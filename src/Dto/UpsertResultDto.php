<?php
declare(strict_types=1);

namespace App\Dto;

use JsonSerializable;

/**
 * DTO for upsert operation result.
 */
readonly class UpsertResultDto implements JsonSerializable
{
    public const RESULT_CREATED = 'created';
    public const RESULT_UPDATED = 'updated';
    public const RESULT_NOOP = 'noop';

    /**
     * Constructor.
     *
     * @param string $result The result: created, updated, or noop.
     * @param int $applicantJobId The applicant job ID.
     * @param int $applicantId The applicant ID.
     */
    public function __construct(
        public string $result,
        public int $applicantJobId,
        public int $applicantId,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'result' => $this->result,
            'applicant_job_id' => $this->applicantJobId,
            'applicant_id' => $this->applicantId,
        ];
    }
}
