<?php
declare(strict_types=1);

namespace App\Dto;

use App\Model\Entity\Applicant;

/**
 * Result of an applicant upsert operation.
 */
readonly class ApplicantUpsertResult
{
    /**
     * Constructor.
     *
     * @param \App\Model\Entity\Applicant $applicant The upserted applicant.
     * @param bool $wasCreated Whether the applicant was created (vs updated).
     */
    public function __construct(
        public Applicant $applicant,
        public bool $wasCreated,
    ) {
    }
}
