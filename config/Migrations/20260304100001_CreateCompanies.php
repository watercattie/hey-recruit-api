<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateCompanies extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('companies');
        $table->addColumn('name', 'string', [
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'null' => false,
        ]);
        $table->create();
    }
}
