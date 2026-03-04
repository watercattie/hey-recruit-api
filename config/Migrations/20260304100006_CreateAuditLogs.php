<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateAuditLogs extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('audit_logs');
        $table->addColumn('api_token_id', 'biginteger', [
            'null' => true,
        ]);
        $table->addColumn('entity_type', 'string', [
            'limit' => 50,
            'null' => false,
        ]);
        $table->addColumn('entity_id', 'biginteger', [
            'null' => true,
        ]);
        $table->addColumn('action', 'string', [
            'limit' => 50,
            'null' => false,
        ]);
        $table->addColumn('result', 'string', [
            'limit' => 20,
            'null' => false,
        ]);
        $table->addColumn('payload', 'json', [
            'null' => true,
        ]);
        $table->addColumn('created', 'datetime', [
            'null' => false,
        ]);

        $table->addIndex(['api_token_id']);
        $table->addIndex(['entity_type', 'entity_id']);
        $table->addIndex(['created']);

        $table->addForeignKey('api_token_id', 'api_tokens', 'id', [
            'delete' => 'SET_NULL',
            'update' => 'NO_ACTION',
        ]);

        $table->create();
    }
}
