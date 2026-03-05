<?php
declare(strict_types=1);

namespace App\Dto;

use App\Model\Entity\ApplicantJob;

/**
 * Result of an applicant-job upsert operation.
 */
readonly class ApplicantJobUpsertResult
{
    /**
     * Constructor.
     *
     * @param \App\Model\Entity\ApplicantJob $applicantJob The upserted applicant-job.
     * @param bool $wasCreated Whether the record was created.
     * @param bool $wasUpdated Whether the record was updated.
     */
    public function __construct(
        public ApplicantJob $applicantJob,
        public bool $wasCreated,
        public bool $wasUpdated,
    ) {
    }
}
