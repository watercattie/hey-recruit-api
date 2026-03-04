<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateApiTokens extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('api_tokens');
        $table->addColumn('company_id', 'biginteger', [
            'null' => false,
        ]);
        $table->addColumn('token', 'string', [
            'limit' => 64,
            'null' => false,
        ]);
        $table->addColumn('name', 'string', [
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('is_active', 'boolean', [
            'default' => true,
            'null' => false,
        ]);
        $table->addColumn('last_used_at', 'datetime', [
            'null' => true,
        ]);
        $table->addColumn('created', 'datetime', [
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'null' => false,
        ]);

        $table->addIndex(['company_id']);
        $table->addIndex(['token'], ['unique' => true]);

        $table->addForeignKey('company_id', 'companies', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ]);

        $table->create();
    }
}
