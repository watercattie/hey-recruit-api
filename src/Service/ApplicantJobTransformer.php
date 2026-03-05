<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\ApplicantJobResponseDto;
use App\Model\Entity\ApplicantJob;

/**
 * Transformer for ApplicantJob entities to DTOs.
 */
class ApplicantJobTransformer
{
    /**
     * Transform a single ApplicantJob entity to DTO.
     *
     * @param \App\Model\Entity\ApplicantJob $entity The entity to transform.
     * @return \App\Dto\ApplicantJobResponseDto
     */
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

    /**
     * Transform a collection of ApplicantJob entities to DTOs.
     *
     * @param iterable<\App\Model\Entity\ApplicantJob|\Cake\Datasource\EntityInterface> $entities The entities to transform.
     * @return array<\App\Dto\ApplicantJobResponseDto>
     */
    public function transformAll(iterable $entities): array
    {
        $result = [];
        /** @var \App\Model\Entity\ApplicantJob $entity */
        foreach ($entities as $entity) {
            $result[] = $this->transform($entity);
        }

        return $result;
    }
}
