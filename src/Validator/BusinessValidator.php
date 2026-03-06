<?php
declare(strict_types=1);

namespace App\Validator;

use App\Dto\ApplicantJobUpsertRequestDto;
use App\Model\Table\JobsTable;
use Cake\Datasource\Exception\RecordNotFoundException;

class BusinessValidator
{
    public function __construct(
        private JobsTable $jobsTable,
    ) {
    }

    public function validateApplicantJobUpsert(
        ApplicantJobUpsertRequestDto $request,
        int $companyId,
    ): array {
        $errors = [];

        $identifierError = $this->validateApplicantIdentifier($request);
        if ($identifierError !== null) {
            $errors['applicant.external_id'] = [$identifierError];
        }

        $jobError = $this->validateJobOwnership($request->jobId, $companyId);
        if ($jobError !== null) {
            $errors['job_id'] = [$jobError];
        }

        return $errors;
    }

    private function validateApplicantIdentifier(ApplicantJobUpsertRequestDto $request): ?string
    {
        $hasExternalId = $request->applicant->externalId !== null
            && trim($request->applicant->externalId) !== '';
        $hasEmail = $request->applicant->email !== null
            && trim($request->applicant->email) !== '';

        if (!$hasExternalId && !$hasEmail) {
            return 'external_id or email is required';
        }

        return null;
    }

    private function validateJobOwnership(int $jobId, int $companyId): ?string
    {
        try {
            /** @var \App\Model\Entity\Job $job */
            $job = $this->jobsTable->get($jobId);
            if ($job->company_id !== $companyId) {
                return 'Job does not exist';
            }
        } catch (RecordNotFoundException $e) {
            return 'Job does not exist';
        }

        return null;
    }
}
