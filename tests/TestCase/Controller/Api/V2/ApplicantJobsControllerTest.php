<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api\V2;

use App\Repository\ApplicantJobRepository;
use App\Service\ApplicantJobTransformer;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * ApplicantJobsController Integration Tests
 *
 * Tests the GET /api/v2/applicant-jobs endpoint.
 */
class ApplicantJobsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Companies',
        'app.ApiTokens',
        'app.Applicants',
        'app.Jobs',
        'app.ApplicantJobs',
    ];

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Register services in container for action injection
        /** @var ApplicantJobsTable $table */
        $table = $this->fetchTable('ApplicantJobs');
        $this->mockService(ApplicantJobRepository::class, fn() => new ApplicantJobRepository($table));
        $this->mockService(ApplicantJobTransformer::class, fn() => new ApplicantJobTransformer());
    }

    /**
     * Configure request with Company 1 token (default).
     *
     * @return void
     */
    protected function useCompany1Token(): void
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . str_repeat('a', 64),
            ],
        ]);
    }

    /**
     * Configure request with Company 2 token.
     *
     * @return void
     */
    protected function useCompany2Token(): void
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . str_repeat('c', 64),
            ],
        ]);
    }

    /**
     * Test index returns correct response structure
     */
    public function testIndexReturnsCorrectStructure(): void
    {
        $this->useCompany1Token();
        $this->get('/api/v2/applicant-jobs');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);

        // Check first item has all expected fields
        $item = $response['data'][0];
        $this->assertArrayHasKey('applicant_external_id', $item);
        $this->assertArrayHasKey('job_external_id', $item);
        $this->assertArrayHasKey('status', $item);
        $this->assertArrayHasKey('applied_at', $item);
        $this->assertArrayHasKey('created', $item);
        $this->assertArrayHasKey('modified', $item);
    }

    /**
     * Test company 1 sees only its own data (3 records)
     */
    public function testCompany1SeesOnlyOwnData(): void
    {
        $this->useCompany1Token();
        $this->get('/api/v2/applicant-jobs');

        $this->assertResponseOk();
        $response = json_decode((string)$this->_response->getBody(), true);

        // Company 1 has 3 applicant-jobs in fixtures (IDs 1, 2, 3)
        $this->assertCount(3, $response['data']);

        // Verify all returned items belong to company 1 applicants/jobs
        $applicantExternalIds = array_column($response['data'], 'applicant_external_id');
        $jobExternalIds = array_column($response['data'], 'job_external_id');

        // Company 1 applicants: EXT-1, EXT-2
        foreach ($applicantExternalIds as $id) {
            $this->assertContains($id, ['EXT-1', 'EXT-2']);
        }

        // Company 1 jobs: JOB-1, JOB-2
        foreach ($jobExternalIds as $id) {
            $this->assertContains($id, ['JOB-1', 'JOB-2']);
        }
    }

    /**
     * Test company 2 sees only its own data (1 record)
     */
    public function testCompany2SeesOnlyOwnData(): void
    {
        $this->useCompany2Token();
        $this->get('/api/v2/applicant-jobs');

        $this->assertResponseOk();
        $response = json_decode((string)$this->_response->getBody(), true);

        // Company 2 has 1 applicant-job in fixtures (ID 4)
        $this->assertCount(1, $response['data']);

        $item = $response['data'][0];
        $this->assertEquals('EXT-3', $item['applicant_external_id']);
        $this->assertEquals('JOB-3', $item['job_external_id']);
        $this->assertEquals('hired', $item['status']);
    }

    /**
     * Test response contains correct data values
     */
    public function testIndexReturnsCorrectValues(): void
    {
        $this->useCompany1Token();
        $this->get('/api/v2/applicant-jobs');

        $this->assertResponseOk();
        $response = json_decode((string)$this->_response->getBody(), true);

        // Find the "new" status entry (ID 1 in fixtures)
        $newEntry = null;
        foreach ($response['data'] as $item) {
            if ($item['status'] === 'new') {
                $newEntry = $item;
                break;
            }
        }

        $this->assertNotNull($newEntry, 'Should have an entry with status "new"');
        $this->assertEquals('EXT-1', $newEntry['applicant_external_id']);
        $this->assertEquals('JOB-1', $newEntry['job_external_id']);
        $this->assertNotNull($newEntry['applied_at']);
    }

    /**
     * Test empty result for company with no applicant-jobs
     */
    public function testEmptyResultWhenNoData(): void
    {
        // Create a new company with token but no applicant-jobs
        $apiTokensTable = $this->getTableLocator()->get('ApiTokens');
        $companiesTable = $this->getTableLocator()->get('Companies');

        $company = $companiesTable->newEntity([
            'name' => 'Empty Company',
            'external_id' => 'EMPTY-1',
        ]);
        $companiesTable->saveOrFail($company);

        /** @var int $companyId */
        $companyId = $company->get('id');

        $token = $apiTokensTable->newEntity([
            'company_id' => $companyId,
            'token' => str_repeat('z', 64),
            'name' => 'Empty Company Token',
            'is_active' => true,
        ]);
        $apiTokensTable->saveOrFail($token);

        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . str_repeat('z', 64),
            ],
        ]);
        $this->get('/api/v2/applicant-jobs');

        $this->assertResponseOk();
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertEmpty($response['data']);
    }

    /**
     * Test view returns single applicant-job
     */
    public function testViewReturnsSingleItem(): void
    {
        $this->useCompany1Token();
        $this->get('/api/v2/applicant-jobs/1');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('applicant_external_id', $response['data']);
        $this->assertArrayHasKey('job_external_id', $response['data']);
        $this->assertArrayHasKey('status', $response['data']);
    }

    /**
     * Test view returns 404 for non-existent ID
     */
    public function testViewReturns404ForNonExistentId(): void
    {
        $this->useCompany1Token();
        $this->get('/api/v2/applicant-jobs/9999');

        $this->assertResponseCode(404);
    }

    /**
     * Test view returns 404 for other company's data (tenant isolation)
     */
    public function testViewReturns404ForOtherCompanyData(): void
    {
        $this->useCompany1Token();
        // ID 4 belongs to company 2
        $this->get('/api/v2/applicant-jobs/4');

        $this->assertResponseCode(404);
    }

    /**
     * Test view requires authentication
     */
    public function testViewRequiresAuth(): void
    {
        $this->configRequest([
            'headers' => ['Accept' => 'application/json'],
        ]);
        $this->get('/api/v2/applicant-jobs/1');

        $this->assertResponseCode(401);
    }
}
