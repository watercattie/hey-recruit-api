<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class CompaniesTable extends Table
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

        $this->setTable('companies');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('ApiTokens', [
            'foreignKey' => 'company_id',
            'dependent' => true,
        ]);
        $this->hasMany('Applicants', [
            'foreignKey' => 'company_id',
            'dependent' => true,
        ]);
        $this->hasMany('Jobs', [
            'foreignKey' => 'company_id',
            'dependent' => true,
        ]);
    }
}
