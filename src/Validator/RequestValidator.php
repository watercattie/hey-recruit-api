<?php
declare(strict_types=1);

namespace App\Validator;

use App\Enum\ApplicationStatus;
use Cake\Collection\Collection;
use Cake\Validation\Validator;
use DateTime;

class RequestValidator
{
    public function validateApplicantJobUpsert(array $data): array
    {
        $validator = (new Validator())
            ->requirePresence('job_id', true, 'job_id is required')
            ->integer('job_id', 'job_id must be an integer')
            ->allowEmptyString('status')
            ->inList(
                'status',
                ApplicationStatus::values(),
                'status must be one of: ' . implode(', ', ApplicationStatus::values()),
            )
            ->allowEmptyString('applied_at')
            ->add('applied_at', 'dateTime', [
                'rule' => fn($value) => $this->isValidDateTime((string)$value),
                'message' => 'applied_at must be a valid datetime',
            ])
            ->requirePresence('applicant', true, 'applicant object is required')
            ->array('applicant', 'applicant object is required');

        $errors = $this->flattenErrors($validator->validate($data));

        if (is_array($data['applicant'] ?? null)) {
            foreach ($this->validateApplicant($data['applicant']) as $field => $msgs) {
                $errors["applicant.{$field}"] = $msgs;
            }
        }

        return $errors;
    }

    private function validateApplicant(array $data): array
    {
        $validator = (new Validator())
            ->allowEmptyString('email')
            ->email('email', false, 'email must be a valid email address');

        return $this->flattenErrors($validator->validate($data));
    }

    private function flattenErrors(array $errors): array
    {
        $result = [];
        foreach ($errors as $field => $rules) {
            $result[$field] = array_values($rules);
        }

        return $result;
    }

    private function isValidDateTime(string $value): bool
    {
        $formats = ['Y-m-d H:i:s', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i:sP', 'Y-m-d'];

        $matchesFormat = (new Collection($formats))->some(
            fn(string $format): bool => DateTime::createFromFormat($format, $value) !== false,
        );

        return $matchesFormat || strtotime($value) !== false;
    }
}
