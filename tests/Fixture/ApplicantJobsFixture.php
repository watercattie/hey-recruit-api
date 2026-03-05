<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ApplicantJobsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'applicant_id' => 1,
                'job_id' => 1,
                'status' => 'new',
                'applied_at' => '2026-03-04 10:00:00',
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
            [
                'id' => 2,
                'applicant_id' => 1,
                'job_id' => 2,
                'status' => 'screening',
                'applied_at' => '2026-03-04 10:00:00',
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
            [
                'id' => 3,
                'applicant_id' => 2,
                'job_id' => 1,
                'status' => 'interview',
                'applied_at' => '2026-03-04 10:00:00',
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
            [
                'id' => 4,
                'applicant_id' => 3,
                'job_id' => 3,
                'status' => 'hired',
                'applied_at' => '2026-03-04 10:00:00',
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
        ];
        parent::init();
    }
}
