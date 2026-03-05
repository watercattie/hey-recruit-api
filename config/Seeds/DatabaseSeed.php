<?php
declare(strict_types=1);

use Cake\I18n\DateTime;
use Migrations\BaseSeed;

class DatabaseSeed extends BaseSeed
{
    public function run(): void
    {
        $now = DateTime::now()->format('Y-m-d H:i:s');

        // Companies
        $companies = [
            ['id' => 1, 'name' => 'Acme Corp', 'created' => $now, 'modified' => $now],
            ['id' => 2, 'name' => 'Test GmbH', 'created' => $now, 'modified' => $now],
        ];
        $this->table('companies')->insert($companies)->saveData();

        // API Tokens (2 per company: 1 active, 1 inactive)
        $apiTokens = [
            // Acme Corp
            ['id' => 1, 'company_id' => 1, 'token' => str_repeat('a', 64), 'name' => 'Main Integration', 'is_active' => true, 'created' => $now, 'modified' => $now],
            ['id' => 2, 'company_id' => 1, 'token' => str_repeat('b', 64), 'name' => 'Old Integration', 'is_active' => false, 'created' => $now, 'modified' => $now],
            // Test GmbH
            ['id' => 3, 'company_id' => 2, 'token' => str_repeat('c', 64), 'name' => 'Primary API', 'is_active' => true, 'created' => $now, 'modified' => $now],
            ['id' => 4, 'company_id' => 2, 'token' => str_repeat('d', 64), 'name' => 'Disabled API', 'is_active' => false, 'created' => $now, 'modified' => $now],
        ];
        $this->table('api_tokens')->insert($apiTokens)->saveData();

        // Jobs (5 per company)
        $jobs = [];
        $jobId = 1;
        foreach ([1, 2] as $companyId) {
            for ($i = 1; $i <= 5; $i++) {
                $jobs[] = [
                    'id' => $jobId,
                    'company_id' => $companyId,
                    'external_id' => "JOB-{$companyId}-{$i}",
                    'title' => "Position {$i} at Company {$companyId}",
                    'status' => 'active',
                    'created' => $now,
                    'modified' => $now,
                ];
                $jobId++;
            }
        }
        $this->table('jobs')->insert($jobs)->saveData();

        // Applicants (10 per company)
        $applicants = [];
        $applicantId = 1;
        foreach ([1, 2] as $companyId) {
            for ($i = 1; $i <= 10; $i++) {
                $applicants[] = [
                    'id' => $applicantId,
                    'company_id' => $companyId,
                    'external_id' => "EXT-{$companyId}-{$i}",
                    'email' => "applicant{$i}@company{$companyId}.test",
                    'first_name' => "First{$i}",
                    'last_name' => "Last{$i}",
                    'phone' => "+49 123 45678{$i}",
                    'created' => $now,
                    'modified' => $now,
                ];
                $applicantId++;
            }
        }
        $this->table('applicants')->insert($applicants)->saveData();

        // ApplicantJobs (10 per company - unique combinations only)
        $statuses = ['new', 'screening', 'interview', 'offer', 'hired', 'rejected'];
        $applicantJobs = [];
        $ajId = 1;

        // Company 1: Applicants 1-10, Jobs 1-5 (each applicant gets one unique job)
        foreach ([1, 2] as $companyId) {
            $baseApplicant = ($companyId - 1) * 10 + 1;
            $baseJob = ($companyId - 1) * 5 + 1;

            // 10 unique combinations per company
            for ($i = 0; $i < 10; $i++) {
                $applicantJobs[] = [
                    'id' => $ajId,
                    'applicant_id' => $baseApplicant + $i,
                    'job_id' => $baseJob + ($i % 5),
                    'status' => $statuses[$i % count($statuses)],
                    'applied_at' => $now,
                    'created' => $now,
                    'modified' => $now,
                ];
                $ajId++;
            }
        }
        $this->table('applicant_jobs')->insert($applicantJobs)->saveData();
    }
}
