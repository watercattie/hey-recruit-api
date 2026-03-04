<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateApplicantJobs extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('applicant_jobs');
        $table->addColumn('applicant_id', 'biginteger', [
            'null' => false,
        ]);
        $table->addColumn('job_id', 'biginteger', [
            'null' => false,
        ]);
        $table->addColumn('status', 'string', [
            'limit' => 20,
            'default' => 'new',
            'null' => false,
        ]);
        $table->addColumn('applied_at', 'datetime', [
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'null' => false,
        ]);

        $table->addIndex(['applicant_id']);
        $table->addIndex(['job_id']);
        $table->addIndex(['applicant_id', 'job_id'], ['unique' => true]);

        $table->addForeignKey('applicant_id', 'applicants', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ]);
        $table->addForeignKey('job_id', 'jobs', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ]);

        $table->create();
    }
}
