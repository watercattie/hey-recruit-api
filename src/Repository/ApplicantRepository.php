<?php
declare(strict_types=1);

namespace App\Repository;

use App\Dto\ApplicantRequestDto;
use App\Dto\ApplicantUpsertResult;
use App\Model\Entity\Applicant;
use App\Model\Table\ApplicantsTable;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Repository for Applicant data access.
 */
class ApplicantRepository
{
    use LocatorAwareTrait;

    private ApplicantsTable $table;

    /**
     * Constructor.
     *
     * @param \App\Model\Table\ApplicantsTable|null $table Table instance for testing.
     */
    public function __construct(?ApplicantsTable $table = null)
    {
        if ($table !== null) {
            $this->table = $table;
        } else {
            /** @var \App\Model\Table\ApplicantsTable $t */
            $t = $this->fetchTable('Applicants');
            $this->table = $t;
        }
    }

    /**
     * Find applicant by external_id or email.
     *
     * @param int $companyId The company ID.
     * @param string|null $externalId External ID to search.
     * @param string|null $email Email to search.
     * @return \App\Model\Entity\Applicant|null
     */
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

    /**
     * Create new applicant entity (not saved).
     *
     * @param \App\Dto\ApplicantRequestDto $dto The applicant data.
     * @param int $companyId The company ID.
     * @return \App\Model\Entity\Applicant
     */
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

    /**
     * Update existing applicant with new data.
     *
     * @param \App\Model\Entity\Applicant $applicant The applicant to update.
     * @param \App\Dto\ApplicantRequestDto $dto The new data.
     * @return void
     */
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

    /**
     * Upsert applicant: find existing or create new, update data, and save.
     *
     * @param \App\Dto\ApplicantRequestDto $dto The applicant data.
     * @param int $companyId The company ID.
     * @return \App\Dto\ApplicantUpsertResult
     * @throws \Cake\ORM\Exception\PersistenceFailedException On save failure.
     */
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

    /**
     * Get the table instance (for transaction handling).
     *
     * @return \App\Model\Table\ApplicantsTable
     */
    public function getTable(): ApplicantsTable
    {
        return $this->table;
    }
}
