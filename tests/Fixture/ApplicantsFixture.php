<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ApplicantsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'company_id' => 1,
                'external_id' => 'EXT-1',
                'email' => 'applicant1@acme.test',
                'first_name' => 'Max',
                'last_name' => 'Mustermann',
                'phone' => '+49 123 456789',
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
            [
                'id' => 2,
                'company_id' => 1,
                'external_id' => 'EXT-2',
                'email' => 'applicant2@acme.test',
                'first_name' => 'Erika',
                'last_name' => 'Musterfrau',
                'phone' => '+49 123 456780',
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
            [
                'id' => 3,
                'company_id' => 2,
                'external_id' => 'EXT-3',
                'email' => 'applicant3@test.de',
                'first_name' => 'Hans',
                'last_name' => 'Test',
                'phone' => null,
                'created' => '2026-03-04 10:00:00',
                'modified' => '2026-03-04 10:00:00',
            ],
        ];
        parent::init();
    }
}
