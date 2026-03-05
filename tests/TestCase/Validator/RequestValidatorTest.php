<?php
declare(strict_types=1);

namespace App\Test\TestCase\Validator;

use App\Validator\RequestValidator;
use Cake\TestSuite\TestCase;

/**
 * RequestValidator Unit Tests
 *
 * Tests schema validation (required, format, enum).
 */
class RequestValidatorTest extends TestCase
{
    private RequestValidator $validator;

    public function setUp(): void
    {
        parent::setUp();
        $this->validator = new RequestValidator();
    }

    /**
     * Test valid request passes validation
     */
    public function testValidRequestPasses(): void
    {
        $data = [
            'applicant' => [
                'external_id' => 'EXT-123',
                'email' => 'test@example.com',
            ],
            'job_id' => 1,
            'status' => 'new',
            'applied_at' => '2024-01-15 10:00:00',
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertEmpty($errors);
    }

    /**
     * Test minimal valid request (only required fields)
     */
    public function testMinimalValidRequest(): void
    {
        $data = [
            'applicant' => [
                'external_id' => 'EXT-123',
            ],
            'job_id' => 1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertEmpty($errors);
    }

    /**
     * Test missing job_id returns error
     */
    public function testMissingJobId(): void
    {
        $data = [
            'applicant' => [
                'external_id' => 'EXT-123',
            ],
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertArrayHasKey('job_id', $errors);
        $this->assertStringContainsString('required', $errors['job_id'][0]);
    }

    /**
     * Test non-numeric job_id returns error
     */
    public function testNonNumericJobId(): void
    {
        $data = [
            'applicant' => [
                'external_id' => 'EXT-123',
            ],
            'job_id' => 'abc',
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertArrayHasKey('job_id', $errors);
        $this->assertStringContainsString('numeric', $errors['job_id'][0]);
    }

    /**
     * Test invalid status returns error
     */
    public function testInvalidStatus(): void
    {
        $data = [
            'applicant' => [
                'external_id' => 'EXT-123',
            ],
            'job_id' => 1,
            'status' => 'invalid_status',
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertArrayHasKey('status', $errors);
        $this->assertStringContainsString('must be one of', $errors['status'][0]);
    }

    /**
     * Test valid status passes
     */
    public function testValidStatus(): void
    {
        $data = [
            'applicant' => [
                'external_id' => 'EXT-123',
            ],
            'job_id' => 1,
            'status' => 'interview',
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertEmpty($errors);
    }

    /**
     * Test invalid applied_at returns error
     */
    public function testInvalidAppliedAt(): void
    {
        $data = [
            'applicant' => [
                'external_id' => 'EXT-123',
            ],
            'job_id' => 1,
            'applied_at' => 'not-a-date',
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertArrayHasKey('applied_at', $errors);
        $this->assertStringContainsString('valid datetime', $errors['applied_at'][0]);
    }

    /**
     * Test missing applicant returns error
     */
    public function testMissingApplicant(): void
    {
        $data = [
            'job_id' => 1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertArrayHasKey('applicant', $errors);
    }

    /**
     * Test missing identifier returns error
     */
    public function testMissingIdentifier(): void
    {
        $data = [
            'applicant' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            'job_id' => 1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertArrayHasKey('applicant.external_id', $errors);
        $this->assertStringContainsString('external_id or email is required', $errors['applicant.external_id'][0]);
    }

    /**
     * Test email as identifier is valid
     */
    public function testEmailAsIdentifier(): void
    {
        $data = [
            'applicant' => [
                'email' => 'test@example.com',
            ],
            'job_id' => 1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertEmpty($errors);
    }

    /**
     * Test invalid email format
     */
    public function testInvalidEmailFormat(): void
    {
        $data = [
            'applicant' => [
                'email' => 'not-an-email',
            ],
            'job_id' => 1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertArrayHasKey('applicant.email', $errors);
    }

    /**
     * Test all errors are collected
     */
    public function testAllErrorsCollected(): void
    {
        $data = [
            'applicant' => [
                'first_name' => 'John',
            ],
            'status' => 'invalid',
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        // Multiple errors should be present
        $this->assertArrayHasKey('job_id', $errors);
        $this->assertArrayHasKey('status', $errors);
        $this->assertArrayHasKey('applicant.external_id', $errors);
    }

    // ========== EDGE CASES ==========

    /**
     * Test empty string as external_id is rejected
     */
    public function testEmptyStringExternalIdIsRejected(): void
    {
        $data = [
            'applicant' => [
                'external_id' => '',
            ],
            'job_id' => 1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertArrayHasKey('applicant.external_id', $errors);
    }

    /**
     * Test whitespace-only external_id is rejected
     */
    public function testWhitespaceOnlyExternalIdIsRejected(): void
    {
        $data = [
            'applicant' => [
                'external_id' => '   ',
            ],
            'job_id' => 1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertArrayHasKey('applicant.external_id', $errors);
    }

    /**
     * Test empty string as email is rejected
     */
    public function testEmptyStringEmailIsRejected(): void
    {
        $data = [
            'applicant' => [
                'email' => '',
            ],
            'job_id' => 1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertArrayHasKey('applicant.external_id', $errors);
    }

    /**
     * Test empty string as job_id is rejected
     */
    public function testEmptyStringJobIdIsRejected(): void
    {
        $data = [
            'applicant' => [
                'external_id' => 'EXT-123',
            ],
            'job_id' => '',
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertArrayHasKey('job_id', $errors);
    }

    /**
     * Test negative job_id is accepted (business validation handles existence)
     */
    public function testNegativeJobIdIsNumeric(): void
    {
        $data = [
            'applicant' => [
                'external_id' => 'EXT-123',
            ],
            'job_id' => -1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        // Schema validation passes (numeric), business validation will catch it
        $this->assertArrayNotHasKey('job_id', $errors);
    }

    /**
     * Test zero job_id is accepted (business validation handles existence)
     */
    public function testZeroJobIdIsNumeric(): void
    {
        $data = [
            'applicant' => [
                'external_id' => 'EXT-123',
            ],
            'job_id' => 0,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        // Schema validation passes (numeric), business validation will catch it
        $this->assertArrayNotHasKey('job_id', $errors);
    }

    /**
     * Test Unicode characters in external_id are accepted
     */
    public function testUnicodeExternalIdIsAccepted(): void
    {
        $data = [
            'applicant' => [
                'external_id' => 'ÄÖÜ-öäü-ß-中文-🎉',
            ],
            'job_id' => 1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertEmpty($errors);
    }

    /**
     * Test Unicode characters in names are accepted
     */
    public function testUnicodeNamesAreAccepted(): void
    {
        $data = [
            'applicant' => [
                'external_id' => 'EXT-123',
                'first_name' => 'José María',
                'last_name' => "O'Connor-Müller",
            ],
            'job_id' => 1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertEmpty($errors);
    }

    /**
     * Test SQL injection attempt in external_id is just a string
     */
    public function testSqlInjectionAttemptIsJustString(): void
    {
        $data = [
            'applicant' => [
                'external_id' => "'; DROP TABLE applicants; --",
            ],
            'job_id' => 1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        // No validation error - it's just a string, ORM handles escaping
        $this->assertEmpty($errors);
    }

    /**
     * Test XSS attempt in names is just a string
     */
    public function testXssAttemptIsJustString(): void
    {
        $data = [
            'applicant' => [
                'external_id' => 'EXT-123',
                'first_name' => '<script>alert("xss")</script>',
            ],
            'job_id' => 1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        // No validation error - API returns JSON, no HTML rendering
        $this->assertEmpty($errors);
    }

    /**
     * Test various valid datetime formats
     */
    public function testVariousDatetimeFormats(): void
    {
        $validFormats = [
            '2024-01-15 10:00:00',
            '2024-01-15T10:00:00',
            '2024-01-15T10:00:00+00:00',
            '2024-01-15',
        ];

        foreach ($validFormats as $format) {
            $data = [
                'applicant' => ['external_id' => 'EXT-123'],
                'job_id' => 1,
                'applied_at' => $format,
            ];

            $errors = $this->validator->validateApplicantJobUpsert($data);

            $this->assertArrayNotHasKey('applied_at', $errors, "Format $format should be valid");
        }
    }

    /**
     * Test all valid status values
     */
    public function testAllValidStatusValues(): void
    {
        $validStatuses = ['new', 'screening', 'interview', 'offer', 'hired', 'rejected'];

        foreach ($validStatuses as $status) {
            $data = [
                'applicant' => ['external_id' => 'EXT-123'],
                'job_id' => 1,
                'status' => $status,
            ];

            $errors = $this->validator->validateApplicantJobUpsert($data);

            $this->assertArrayNotHasKey('status', $errors, "Status '$status' should be valid");
        }
    }

    /**
     * Test case sensitivity in status (should be case-sensitive)
     */
    public function testStatusIsCaseSensitive(): void
    {
        $data = [
            'applicant' => ['external_id' => 'EXT-123'],
            'job_id' => 1,
            'status' => 'NEW', // Uppercase
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertArrayHasKey('status', $errors);
    }

    /**
     * Test email with plus sign is valid
     */
    public function testEmailWithPlusSignIsValid(): void
    {
        $data = [
            'applicant' => [
                'email' => 'test+tag@example.com',
            ],
            'job_id' => 1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        $this->assertEmpty($errors);
    }

    /**
     * Test international email domains (IDN) require punycode encoding
     *
     * Note: PHP's filter_var does NOT accept raw IDN domains like 'user@例え.jp'.
     * IDN domains must be converted to punycode (e.g., 'user@xn--r8jz45g.jp') before validation.
     * This is handled by the client or a separate punycode conversion step.
     */
    public function testInternationalEmailRequiresPunycode(): void
    {
        // Raw IDN domain is rejected by filter_var
        $data = [
            'applicant' => [
                'email' => 'user@例え.jp',
            ],
            'job_id' => 1,
        ];

        $errors = $this->validator->validateApplicantJobUpsert($data);

        // This is expected - IDN must be punycode-encoded
        $this->assertArrayHasKey('applicant.email', $errors);

        // Punycode-encoded version works
        $data['applicant']['email'] = 'user@xn--r8jz45g.jp';
        $errors = $this->validator->validateApplicantJobUpsert($data);
        $this->assertEmpty($errors);
    }
}
