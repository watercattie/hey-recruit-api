<?php
declare(strict_types=1);

namespace App\Repository;

use App\Dto\ApplicantRequestDto;
use App\Dto\ApplicantUpsertResult;
use App\Model\Entity\Applicant;
use App\Model\Table\ApplicantsTable;

class ApplicantRepository
{
    public function __construct(
        private ApplicantsTable $table,
    ) {
    }

    public function findByIdentifier(int $companyId, ?string $externalId, ?string $email): ?Applicant
    {
        if ($externalId !== null) {
            $result = $this->table->find()
                ->where([
                    'company_id' => $companyId,
                    'external_id' => $externalId,
                ])
                ->first();
            if ($result instanceof Applicant) {
                return $result;
            }
        }

        if ($email !== null) {
            $result = $this->table->find()
                ->where([
                    'company_id' => $companyId,
                    'email' => $email,
                ])
                ->first();
            if ($result instanceof Applicant) {
                return $result;
            }
        }

        return null;
    }

    public function create(ApplicantRequestDto $dto, int $companyId): Applicant
    {
        /** @var \App\Model\Entity\Applicant $applicant */
        $applicant = $this->table->newEntity([
            'company_id' => $companyId,
            'external_id' => $dto->externalId,
            'email' => $dto->email,
            'first_name' => $dto->firstName,
            'last_name' => $dto->lastName,
            'phone' => $dto->phone,
        ]);

        return $applicant;
    }

    public function update(Applicant $applicant, ApplicantRequestDto $dto): void
    {
        if ($dto->externalId !== null) {
            $applicant->external_id = $dto->externalId;
        }
        if ($dto->email !== null) {
            $applicant->email = $dto->email;
        }
        if ($dto->firstName !== null) {
            $applicant->first_name = $dto->firstName;
        }
        if ($dto->lastName !== null) {
            $applicant->last_name = $dto->lastName;
        }
        if ($dto->phone !== null) {
            $applicant->phone = $dto->phone;
        }
    }

    public function upsert(ApplicantRequestDto $dto, int $companyId): ApplicantUpsertResult
    {
        $applicant = $this->findByIdentifier($companyId, $dto->externalId, $dto->email);
        $created = false;

        if ($applicant === null) {
            $applicant = $this->create($dto, $companyId);
            $created = true;
        } else {
            $this->update($applicant, $dto);
        }

        $this->table->saveOrFail($applicant);

        return new ApplicantUpsertResult($applicant, $created);
    }

    public function getTable(): ApplicantsTable
    {
        return $this->table;
    }
}
