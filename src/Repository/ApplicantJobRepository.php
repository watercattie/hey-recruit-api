<?php
declare(strict_types=1);

namespace App\Repository;

use App\Dto\ApplicantJobUpsertRequestDto;
use App\Dto\ApplicantJobUpsertResult;
use App\Model\Entity\ApplicantJob;
use App\Model\Table\ApplicantJobsTable;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Repository for ApplicantJob queries.
 *
 * Encapsulates data access logic for applicant-job relationships.
 */
class ApplicantJobRepository
{
    use LocatorAwareTrait;

    private ApplicantJobsTable $table;

    /**
     * Constructor.
     *
     * @param \App\Model\Table\ApplicantJobsTable|null $table Table instance for testing.
     */
    public function __construct(?ApplicantJobsTable $table = null)
    {
        if ($table !== null) {
            $this->table = $table;
        } else {
            /** @var \App\Model\Table\ApplicantJobsTable $t */
            $t = $this->fetchTable('ApplicantJobs');
            $this->table = $t;
        }
    }

    /**
     * Get all applicant-jobs for a company.
     *
     * Note: Pagination planned for future version.
     *
     * @param int $companyId The company ID.
     * @return iterable<\App\Model\Entity\ApplicantJob>
     */
    public function findForCompany(int $companyId): iterable
    {
        /** @var iterable<\App\Model\Entity\ApplicantJob> $items */
        $items = $this->table
            ->find('forCompany', companyId: $companyId)
            ->orderBy(['ApplicantJobs.created' => 'DESC'])
            ->all();

        return $items;
    }

    /**
     * Find applicant-job by ID for a specific company.
     *
     * @param int $id The applicant-job ID.
     * @param int $companyId The company ID.
     * @return \App\Model\Entity\ApplicantJob|null
     */
    public function findByIdForCompany(int $id, int $companyId): ?ApplicantJob
    {
        $result = $this->table
            ->find('forCompany', companyId: $companyId)
            ->where(['ApplicantJobs.id' => $id])
            ->first();

        return $result instanceof ApplicantJob ? $result : null;
    }

    /**
     * Find applicant-job by applicant and job ID.
     *
     * @param int $applicantId The applicant ID.
     * @param int $jobId The job ID.
     * @return \App\Model\Entity\ApplicantJob|null
     */
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

    /**
     * Create new applicant-job entity (not saved).
     *
     * @param int $applicantId The applicant ID.
     * @param int $jobId The job ID.
     * @param string $status The status.
     * @param \Cake\I18n\DateTime $appliedAt When applied.
     * @return \App\Model\Entity\ApplicantJob
     */
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

    /**
     * Update existing applicant-job.
     *
     * @param \App\Model\Entity\ApplicantJob $entity The entity to update.
     * @param \App\Dto\ApplicantJobUpsertRequestDto $request The new data.
     * @return bool True if any changes were made.
     */
    public function update(ApplicantJob $entity, ApplicantJobUpsertRequestDto $request): bool
    {
        $changed = false;

        if ($entity->status !== $request->status) {
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

    /**
     * Upsert applicant-job: find existing or create new, update data, and save.
     *
     * @param int $applicantId The applicant ID.
     * @param \App\Dto\ApplicantJobUpsertRequestDto $request The request data.
     * @return \App\Dto\ApplicantJobUpsertResult
     * @throws \Cake\ORM\Exception\PersistenceFailedException On save failure.
     */
    public function upsert(int $applicantId, ApplicantJobUpsertRequestDto $request): ApplicantJobUpsertResult
    {
        $entity = $this->findByApplicantAndJob($applicantId, $request->jobId);
        $created = false;
        $updated = false;

        if ($entity === null) {
            $entity = $this->create($applicantId, $request->jobId, $request->status, $request->appliedAt);
            $created = true;
        } else {
            $updated = $this->update($entity, $request);
        }

        $this->table->saveOrFail($entity);

        return new ApplicantJobUpsertResult($entity, $created, $updated);
    }

    /**
     * Get the table instance (for transaction handling).
     *
     * @return \App\Model\Table\ApplicantJobsTable
     */
    public function getTable(): ApplicantJobsTable
    {
        return $this->table;
    }
}
