<?php
declare(strict_types=1);

namespace App\Test\TestCase\Validator;

use App\Dto\ApplicantJobUpsertRequestDto;
use App\Dto\ApplicantRequestDto;
use App\Validator\BusinessValidator;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;

/**
 * BusinessValidator Unit Tests
 *
 * Tests business rule validation (job existence, ownership).
 */
class BusinessValidatorTest extends TestCase
{
    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Companies',
        'app.Jobs',
    ];

    private BusinessValidator $validator;

    public function setUp(): void
    {
        parent::setUp();
        $this->validator = new BusinessValidator();
    }

    /**
     * Test valid job passes validation
     */
    public function testValidJobPasses(): void
    {
        $request = new ApplicantJobUpsertRequestDto(
            applicant: new ApplicantRequestDto(externalId: 'EXT-123'),
            jobId: 1, // Company 1's job in fixtures
            status: 'new',
            appliedAt: new DateTime(),
        );

        $errors = $this->validator->validateApplicantJobUpsert($request, 1);

        $this->assertEmpty($errors);
    }

    /**
     * Test non-existent job returns error
     */
    public function testNonExistentJob(): void
    {
        $request = new ApplicantJobUpsertRequestDto(
            applicant: new ApplicantRequestDto(externalId: 'EXT-123'),
            jobId: 9999, // Does not exist
            status: 'new',
            appliedAt: new DateTime(),
        );

        $errors = $this->validator->validateApplicantJobUpsert($request, 1);

        $this->assertArrayHasKey('job_id', $errors);
        $this->assertStringContainsString('does not exist', $errors['job_id'][0]);
    }

    /**
     * Test job from other company returns error
     */
    public function testJobFromOtherCompany(): void
    {
        $request = new ApplicantJobUpsertRequestDto(
            applicant: new ApplicantRequestDto(externalId: 'EXT-123'),
            jobId: 3, // Company 2's job in fixtures
            status: 'new',
            appliedAt: new DateTime(),
        );

        // Company 1 trying to use Company 2's job
        $errors = $this->validator->validateApplicantJobUpsert($request, 1);

        $this->assertArrayHasKey('job_id', $errors);
        $this->assertStringContainsString('does not exist', $errors['job_id'][0]);
    }

    /**
     * Test company 2 can access its own jobs
     */
    public function testCompany2CanAccessOwnJobs(): void
    {
        $request = new ApplicantJobUpsertRequestDto(
            applicant: new ApplicantRequestDto(externalId: 'EXT-123'),
            jobId: 3, // Company 2's job in fixtures
            status: 'new',
            appliedAt: new DateTime(),
        );

        // Company 2 accessing its own job
        $errors = $this->validator->validateApplicantJobUpsert($request, 2);

        $this->assertEmpty($errors);
    }
}
