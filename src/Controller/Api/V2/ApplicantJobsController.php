<?php
declare(strict_types=1);

namespace App\Controller\Api\V2;

use App\Controller\Api\ApiController;
use App\Dto\ApplicantJobUpsertRequestDto;
use App\Error\Exception\ValidationException;
use App\Repository\ApplicantJobRepository;
use App\Service\ApplicantJobTransformer;
use App\Service\ApplicantJobUpsertService;
use App\Validator\BusinessValidator;
use App\Validator\RequestValidator;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;

class ApplicantJobsController extends ApiController
{
    public function index(
        ApplicantJobRepository $repository,
        ApplicantJobTransformer $transformer,
    ): void {
        $companyId = $this->getCompanyId();
        $items = $repository->findForCompany($companyId);

        $data = $transformer->transformAll($items);
        $this->set(compact('data'));
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    public function view(
        ApplicantJobRepository $repository,
        ApplicantJobTransformer $transformer,
        string $id,
    ): void {
        $companyId = $this->getCompanyId();
        $item = $repository->findByIdForCompany((int)$id, $companyId);

        if ($item === null) {
            throw new NotFoundException('Applicant job not found');
        }

        $data = $transformer->transform($item);
        $this->set(compact('data'));
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    public function add(
        ApplicantJobUpsertService $upsertService,
        RequestValidator $requestValidator,
        BusinessValidator $businessValidator,
    ): void {
        $requestData = $this->request->getData();
        if (!is_array($requestData) || empty($requestData)) {
            throw new BadRequestException('Request body is required');
        }

        $schemaErrors = $requestValidator->validateApplicantJobUpsert($requestData);
        if (!empty($schemaErrors)) {
            throw new ValidationException('Validation failed', $schemaErrors);
        }

        $dto = ApplicantJobUpsertRequestDto::fromArray($requestData);

        $businessErrors = $businessValidator->validateApplicantJobUpsert($dto, $this->getCompanyId());
        if (!empty($businessErrors)) {
            throw new ValidationException('Validation failed', $businessErrors);
        }

        $result = $upsertService->upsert(
            request: $dto,
            companyId: $this->getCompanyId(),
            apiTokenId: $this->getApiToken()->id,
        );

        $data = $result;
        $this->set(compact('data'));
        $this->viewBuilder()->setOption('serialize', ['data']);
    }
}
