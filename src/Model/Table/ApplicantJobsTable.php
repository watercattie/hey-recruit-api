<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Enum\ApplicationStatus;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ApplicantJobsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('applicant_jobs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Applicants', [
            'foreignKey' => 'applicant_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Jobs', [
            'foreignKey' => 'job_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('status')
            ->maxLength('status', 20)
            ->inList('status', ApplicationStatus::values())
            ->notEmptyString('status');

        $validator
            ->dateTime('applied_at')
            ->requirePresence('applied_at', 'create')
            ->notEmptyDateTime('applied_at');

        $validator
            ->integer('applicant_id')
            ->requirePresence('applicant_id', 'create')
            ->notEmptyString('applicant_id');

        $validator
            ->integer('job_id')
            ->requirePresence('job_id', 'create')
            ->notEmptyString('job_id');

        return $validator;
    }

    /**
     * Find applicant-jobs for a specific company.
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query to modify.
     * @param int $companyId The company ID to filter by.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findForCompany(SelectQuery $query, int $companyId): SelectQuery
    {
        return $query
            ->contain(['Applicants', 'Jobs'])
            ->innerJoinWith('Applicants', function ($q) use ($companyId) {
                return $q->where(['Applicants.company_id' => $companyId]);
            });
    }
}
