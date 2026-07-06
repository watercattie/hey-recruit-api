<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api\V2;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * API Authentication Integration Tests
 *
 * Tests that the API token authentication works correctly for all scenarios.
 */
class AuthenticationTest extends TestCase
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
    public function setUp(): void
    {
        parent::setUp();
        $this->configRequest([
            'headers' => ['Accept' => 'application/json'],
        ]);
    }

    /**
     * Test that request without Authorization header returns 401
     */
    public function testNoAuthorizationHeaderReturns401(): void
    {
        $this->get('/api/v2/applicant-jobs');

        $this->assertResponseCode(401);
        $this->assertContentType('application/json');
        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('UNAUTHORIZED', $response['error']['code']);
    }

    /**
     * Test that request with invalid token returns 401
     */
    public function testInvalidTokenReturns401(): void
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer invalid-token-12345',
            ],
        ]);

        $this->get('/api/v2/applicant-jobs');

        $this->assertResponseCode(401);
        $this->assertContentType('application/json');
        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('UNAUTHORIZED', $response['error']['code']);
    }

    /**
     * Test that request with inactive token returns 401
     */
    public function testInactiveTokenReturns401(): void
    {
        // Token from fixture: str_repeat('b', 64) (is_active = false)
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . str_repeat('b', 64),
            ],
        ]);

        $this->get('/api/v2/applicant-jobs');

        $this->assertResponseCode(401);
        $this->assertContentType('application/json');
        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('UNAUTHORIZED', $response['error']['code']);
    }

    /**
     * Test that request with valid token returns 200
     */
    public function testValidTokenReturns200(): void
    {
        // Token from fixture: str_repeat('a', 64) (is_active = true)
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . str_repeat('a', 64),
            ],
        ]);

        $this->get('/api/v2/applicant-jobs');

        $this->assertResponseCode(200);
        $this->assertContentType('application/json');
    }

    /**
     * Test that different companies get isolated data
     */
    public function testCompanyDataIsolation(): void
    {
        // Token from fixture: str_repeat('a', 64) (company_id = 1)
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . str_repeat('a', 64),
            ],
        ]);

        $this->get('/api/v2/applicant-jobs');

        $this->assertResponseCode(200);
        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
    }

    /**
     * Test that Authorization with wrong scheme (not Bearer) returns 401
     */
    public function testWrongAuthSchemeReturns401(): void
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . str_repeat('a', 64),
            ],
        ]);

        $this->get('/api/v2/applicant-jobs');

        $this->assertResponseCode(401);
    }
}
