<?php
declare(strict_types=1);

namespace App\Validator;

use App\Enum\ApplicationStatus;
use DateTime;

/**
 * Schema validator for API requests.
 *
 * Validates request data structure, types, formats, and enums.
 * Does NOT check business rules (e.g., job ownership).
 */
class RequestValidator
{
    /**
     * Validate applicant-job upsert request schema.
     *
     * @param array<string, mixed> $data The request data.
     * @return array<string, array<string>> Errors by field.
     */
    public function validateApplicantJobUpsert(array $data): array
    {
        $errors = [];

        if (!isset($data['job_id']) || $data['job_id'] === '') {
            $errors['job_id'] = ['job_id is required'];
        } elseif (!is_numeric($data['job_id'])) {
            $errors['job_id'] = ['job_id must be numeric'];
        }

        if (isset($data['status']) && $data['status'] !== '') {
            if (!ApplicationStatus::isValid((string)$data['status'])) {
                $errors['status'] = [
                    'status must be one of: ' . implode(', ', ApplicationStatus::values()),
                ];
            }
        }

        if (isset($data['applied_at']) && $data['applied_at'] !== '') {
            if (!$this->isValidDateTime((string)$data['applied_at'])) {
                $errors['applied_at'] = ['applied_at must be a valid datetime'];
            }
        }

        if (!isset($data['applicant']) || !is_array($data['applicant'])) {
            $errors['applicant'] = ['applicant object is required'];
        } else {
            $applicantErrors = $this->validateApplicant($data['applicant']);
            foreach ($applicantErrors as $field => $fieldErrors) {
                $errors["applicant.{$field}"] = $fieldErrors;
            }
        }

        return $errors;
    }

    /**
     * Validate applicant nested object.
     *
     * @param array<string, mixed> $data The applicant data.
     * @return array<string, array<string>> Errors by field.
     */
    private function validateApplicant(array $data): array
    {
        $errors = [];

        $externalId = isset($data['external_id']) ? trim((string)$data['external_id']) : '';
        $email = isset($data['email']) ? trim((string)$data['email']) : '';

        $hasExternalId = $externalId !== '';
        $hasEmail = $email !== '';

        if (!$hasExternalId && !$hasEmail) {
            $errors['external_id'] = ['external_id or email is required'];
        }

        if ($hasEmail && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['email must be a valid email address'];
        }

        return $errors;
    }

    /**
     * Check if string is valid datetime.
     *
     * @param string $value The value to check.
     * @return bool
     */
    private function isValidDateTime(string $value): bool
    {
        $formats = ['Y-m-d H:i:s', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i:sP', 'Y-m-d'];
        foreach ($formats as $format) {
            $dt = DateTime::createFromFormat($format, $value);
            if ($dt !== false) {
                return true;
            }
        }

        return strtotime($value) !== false;
    }
}
