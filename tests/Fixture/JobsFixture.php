<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class JobsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'company_id' => 1,
                'external_id' => 'JOB-1',
                'title' => 'Senior Developer',
                'status' => 'active',
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
            [
                'id' => 2,
                'company_id' => 1,
                'external_id' => 'JOB-2',
                'title' => 'Project Manager',
                'status' => 'active',
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
            [
                'id' => 3,
                'company_id' => 2,
                'external_id' => 'JOB-3',
                'title' => 'Designer',
                'status' => 'active',
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
        ];
        parent::init();
    }
}
