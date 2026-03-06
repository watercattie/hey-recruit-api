<?php
declare(strict_types=1);

namespace App\Dto;

use App\Model\Entity\ApplicantJob;

readonly class ApplicantJobUpsertResult
{
    public function __construct(
        public ApplicantJob $applicantJob,
        public bool $wasCreated,
        public bool $wasUpdated,
    ) {
    }
}
