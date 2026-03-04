<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateJobs extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('jobs');
        $table->addColumn('company_id', 'biginteger', [
            'null' => false,
        ]);
        $table->addColumn('external_id', 'string', [
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('title', 'string', [
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('status', 'string', [
            'limit' => 20,
            'default' => 'active',
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'null' => false,
        ]);

        $table->addIndex(['company_id']);
        $table->addIndex(['company_id', 'external_id'], ['unique' => true]);

        $table->addForeignKey('company_id', 'companies', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ]);

        $table->create();
    }
}
