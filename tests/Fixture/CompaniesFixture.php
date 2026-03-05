<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CompaniesFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Acme Corp',
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
            [
                'id' => 2,
                'name' => 'Test GmbH',
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
        ];
        parent::init();
    }
}
