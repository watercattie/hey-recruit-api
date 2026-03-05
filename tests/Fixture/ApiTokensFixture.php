<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ApiTokensFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'company_id' => 1,
                'token' => str_repeat('a', 64),
                'name' => 'Main Integration',
                'is_active' => true,
                'last_used_at' => null,
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
            [
                'id' => 2,
                'company_id' => 1,
                'token' => str_repeat('b', 64),
                'name' => 'Inactive Token',
                'is_active' => false,
                'last_used_at' => null,
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
            [
                'id' => 3,
                'company_id' => 2,
                'token' => str_repeat('c', 64),
                'name' => 'Company 2 Token',
                'is_active' => true,
                'last_used_at' => null,
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
        ];
        parent::init();
    }
}
