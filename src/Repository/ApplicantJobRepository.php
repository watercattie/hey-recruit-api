<?php
declare(strict_types=1);

namespace App\Repository;

use App\Dto\ApplicantJobUpsertRequestDto;
use App\Dto\ApplicantJobUpsertResult;
use App\Enum\ApplicationStatus;
use App\Model\Entity\ApplicantJob;
use App\Model\Table\ApplicantJobsTable;
use Cake\I18n\DateTime;

class ApplicantJobRepository
{
    public function __construct(
        private ApplicantJobsTable $table,
    ) {
    }

    public function findForCompany(int $companyId): iterable
    {
        /** @var iterable<\App\Model\Entity\ApplicantJob> $items */
        $items = $this->table
            ->find('forCompany', companyId: $companyId)
            ->orderBy(['ApplicantJobs.created' => 'DESC'])
            ->all();

        return $items;
    }

    public function findByIdForCompany(int $id, int $companyId): ?ApplicantJob
    {
        $result = $this->table
            ->find('forCompany', companyId: $companyId)
            ->where(['ApplicantJobs.id' => $id])
            ->first();

        return $result instanceof ApplicantJob ? $result : null;
    }

    public function findByApplicantAndJob(int $applicantId, int $jobId): ?ApplicantJob
    {
        $result = $this->table->find()
            ->where([
                'applicant_id' => $applicantId,
                'job_id' => $jobId,
            ])
            ->first();

        return $result instanceof ApplicantJob ? $result : null;
    }

    public function create(
        int $applicantId,
        int $jobId,
        string $status,
        DateTime $appliedAt,
    ): ApplicantJob {
        /** @var \App\Model\Entity\ApplicantJob $entity */
        $entity = $this->table->newEntity([
            'applicant_id' => $applicantId,
            'job_id' => $jobId,
            'status' => $status,
            'applied_at' => $appliedAt,
        ]);

        return $entity;
    }

    public function update(ApplicantJob $entity, ApplicantJobUpsertRequestDto $request): bool
    {
        $changed = false;

        if ($request->status !== null && $entity->status !== $request->status) {
            $entity->status = $request->status;
            $changed = true;
        }

        $existingAppliedAt = $entity->applied_at?->format('Y-m-d H:i:s');
        $newAppliedAt = $request->appliedAt->format('Y-m-d H:i:s');
        if ($existingAppliedAt !== $newAppliedAt) {
            $entity->applied_at = $request->appliedAt;
            $changed = true;
        }

        return $changed;
    }

    public function upsert(int $applicantId, ApplicantJobUpsertRequestDto $request): ApplicantJobUpsertResult
    {
        $entity = $this->findByApplicantAndJob($applicantId, $request->jobId);
        $created = false;
        $updated = false;

        if ($entity === null) {
            $status = $request->status ?? ApplicationStatus::New->value;
            $entity = $this->create($applicantId, $request->jobId, $status, $request->appliedAt);
            $created = true;
        } else {
            $updated = $this->update($entity, $request);
        }

        $this->table->saveOrFail($entity);

        return new ApplicantJobUpsertResult($entity, $created, $updated);
    }

    public function getTable(): ApplicantJobsTable
    {
        return $this->table;
    }
}
