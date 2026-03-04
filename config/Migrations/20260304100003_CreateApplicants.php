<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateApplicants extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('applicants');
        $table->addColumn('company_id', 'biginteger', [
            'null' => false,
        ]);
        $table->addColumn('external_id', 'string', [
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('email', 'string', [
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('first_name', 'string', [
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('last_name', 'string', [
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('phone', 'string', [
            'limit' => 50,
            'null' => true,
        ]);
        $table->addColumn('created', 'datetime', [
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'null' => false,
        ]);

        $table->addIndex(['company_id']);
        $table->addIndex(['company_id', 'external_id'], ['unique' => true]);
        $table->addIndex(['company_id', 'email'], ['unique' => true]);

        $table->addForeignKey('company_id', 'companies', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ]);

        $table->create();
    }
}
