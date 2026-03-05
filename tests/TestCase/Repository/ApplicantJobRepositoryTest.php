<?php
declare(strict_types=1);

namespace App\Test\TestCase\Repository;

use App\Repository\ApplicantJobRepository;
use Cake\TestSuite\TestCase;

/**
 * ApplicantJobRepository Integration Tests
 */
class ApplicantJobRepositoryTest extends TestCase
{
    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Companies',
        'app.Applicants',
        'app.Jobs',
        'app.ApplicantJobs',
    ];

    private ApplicantJobRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new ApplicantJobRepository();
    }

    /**
     * Test findForCompany returns only company's records
     */
    public function testFindForCompanyReturnsOnlyCompanyRecords(): void
    {
        // Company 1 has applicant-jobs 1, 2, 3
        $results = iterator_to_array($this->repository->findForCompany(1));

        $this->assertCount(3, $results);
    }

    /**
     * Test findForCompany returns correct company 2 data
     */
    public function testFindForCompanyReturnsCompany2Data(): void
    {
        // Company 2 has applicant-job 4
        $results = iterator_to_array($this->repository->findForCompany(2));

        $this->assertCount(1, $results);
        $this->assertEquals('hired', $results[0]->status);
    }

    /**
     * Test findForCompany returns empty for non-existent company
     */
    public function testFindForCompanyReturnsEmptyForNonExistentCompany(): void
    {
        $results = iterator_to_array($this->repository->findForCompany(999));

        $this->assertEmpty($results);
    }

    /**
     * Test findForCompany contains associated applicant
     */
    public function testFindForCompanyContainsApplicant(): void
    {
        $results = iterator_to_array($this->repository->findForCompany(1));

        $this->assertNotEmpty($results);
        $this->assertNotNull($results[0]->applicant);
        $this->assertNotEmpty($results[0]->applicant->external_id);
    }

    /**
     * Test findForCompany contains associated job
     */
    public function testFindForCompanyContainsJob(): void
    {
        $results = iterator_to_array($this->repository->findForCompany(1));

        $this->assertNotEmpty($results);
        $this->assertNotNull($results[0]->job);
        $this->assertNotEmpty($results[0]->job->external_id);
    }
}
