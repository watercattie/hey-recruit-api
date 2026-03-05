<?php
declare(strict_types=1);

namespace App\Validator;

use App\Dto\ApplicantJobUpsertRequestDto;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Business validator for applicant-job operations.
 *
 * Validates business rules that require database access:
 * - Job exists and belongs to company
 * - Other authorization/state checks
 */
class BusinessValidator
{
    use LocatorAwareTrait;

    /**
     * Validate business rules for applicant-job upsert.
     *
     * @param \App\Dto\ApplicantJobUpsertRequestDto $request The request to validate.
     * @param int $companyId The company ID for authorization checks.
     * @return array<string, array<string>> Errors by field.
     */
    public function validateApplicantJobUpsert(
        ApplicantJobUpsertRequestDto $request,
        int $companyId,
    ): array {
        $errors = [];

        $jobError = $this->validateJobOwnership($request->jobId, $companyId);
        if ($jobError !== null) {
            $errors['job_id'] = [$jobError];
        }

        return $errors;
    }

    /**
     * Validate that job exists and belongs to company.
     *
     * @param int $jobId The job ID.
     * @param int $companyId The company ID.
     * @return string|null Error message or null if valid.
     */
    private function validateJobOwnership(int $jobId, int $companyId): ?string
    {
        $jobsTable = $this->fetchTable('Jobs');

        try {
            /** @var \App\Model\Entity\Job $job */
            $job = $jobsTable->get($jobId);
            if ($job->company_id !== $companyId) {
                return 'Job does not exist';
            }
        } catch (RecordNotFoundException $e) {
            return 'Job does not exist';
        }

        return null;
    }
}
