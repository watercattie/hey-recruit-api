<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\ApplicantJobResponseDto;
use App\Model\Entity\ApplicantJob;
use Cake\Collection\Collection;

class ApplicantJobTransformer
{
    public function transform(ApplicantJob $entity): ApplicantJobResponseDto
    {
        return new ApplicantJobResponseDto(
            applicantExternalId: $entity->applicant->external_id,
            jobExternalId: $entity->job->external_id,
            status: $entity->status,
            appliedAt: $entity->applied_at?->toIso8601String(),
            created: $entity->created->toIso8601String(),
            modified: $entity->modified->toIso8601String(),
        );
    }

    public function transformAll(iterable $entities): array
    {
        return (new Collection($entities))
            ->map(fn($e) => $this->transform($e))
            ->toArray();
    }
}
