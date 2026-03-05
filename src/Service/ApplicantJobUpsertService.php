<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\ApplicantJobUpsertRequestDto;
use App\Dto\UpsertResultDto;
use App\Repository\ApplicantJobRepository;
use App\Repository\ApplicantRepository;

/**
 * Service for upserting applicant-job relationships.
 *
 * Orchestrates the upsert workflow using repositories for data access.
 */
class ApplicantJobUpsertService
{
    /**
     * Constructor.
     *
     * @param \App\Repository\ApplicantRepository $applicantRepository Applicant repository.
     * @param \App\Repository\ApplicantJobRepository $applicantJobRepository ApplicantJob repository.
     * @param \App\Service\AuditLogService $auditLogService Audit log service.
     */
    public function __construct(
        private ApplicantRepository $applicantRepository,
        private ApplicantJobRepository $applicantJobRepository,
        private AuditLogService $auditLogService,
    ) {
    }

    /**
     * Upsert an applicant-job relationship.
     *
     * @param \App\Dto\ApplicantJobUpsertRequestDto $request The request DTO.
     * @param int $companyId The company ID.
     * @param int|null $apiTokenId The API token ID for audit logging.
     * @return \App\Dto\UpsertResultDto
     */
    public function upsert(
        ApplicantJobUpsertRequestDto $request,
        int $companyId,
        ?int $apiTokenId = null,
    ): UpsertResultDto {
        $connection = $this->applicantRepository->getTable()->getConnection();

        return $connection->transactional(function () use ($request, $companyId, $apiTokenId) {
            $applicantResult = $this->applicantRepository->upsert(
                $request->applicant,
                $companyId,
            );

            $applicantJobResult = $this->applicantJobRepository->upsert(
                (int)$applicantResult->applicant->id,
                $request,
            );

            $result = $this->determineResult(
                $applicantResult->wasCreated,
                $applicantJobResult->wasCreated,
                $applicantJobResult->wasUpdated,
            );

            $this->auditLogService->log(
                apiTokenId: $apiTokenId,
                entityType: 'applicant_job',
                entityId: (int)$applicantJobResult->applicantJob->id,
                action: 'upsert',
                result: $result,
            );

            return new UpsertResultDto(
                result: $result,
                applicantJobId: (int)$applicantJobResult->applicantJob->id,
                applicantId: (int)$applicantResult->applicant->id,
            );
        });
    }

    /**
     * Determine the result string based on what happened.
     *
     * @param bool $applicantCreated Was a new applicant created?
     * @param bool $applicantJobCreated Was a new applicant-job created?
     * @param bool $applicantJobUpdated Was the applicant-job updated?
     * @return string The result: created, updated, or noop.
     */
    private function determineResult(
        bool $applicantCreated,
        bool $applicantJobCreated,
        bool $applicantJobUpdated,
    ): string {
        if ($applicantCreated || $applicantJobCreated) {
            return UpsertResultDto::RESULT_CREATED;
        }

        if ($applicantJobUpdated) {
            return UpsertResultDto::RESULT_UPDATED;
        }

        return UpsertResultDto::RESULT_NOOP;
    }
}
