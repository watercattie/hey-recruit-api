<?php
declare(strict_types=1);

namespace App\Dto;

use App\Model\Entity\Applicant;

readonly class ApplicantUpsertResult
{
    public function __construct(
        public Applicant $applicant,
        public bool $wasCreated,
    ) {
    }
}
