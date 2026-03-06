<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\ApplicantJobUpsertRequestDto;
use App\Dto\UpsertResultDto;
use App\Repository\ApplicantJobRepository;
use App\Repository\ApplicantRepository;

class ApplicantJobUpsertService
{
    public function __construct(
        private ApplicantRepository $applicantRepository,
        private ApplicantJobRepository $applicantJobRepository,
        private AuditLogService $auditLogService,
    ) {
    }

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
