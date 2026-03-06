<?php
declare(strict_types=1);

namespace App\Dto;

readonly class ApplicantRequestDto
{
    public function __construct(
        public ?string $externalId = null,
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $phone = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            externalId: isset($data['external_id']) ? (string)$data['external_id'] : null,
            email: isset($data['email']) ? (string)$data['email'] : null,
            firstName: isset($data['first_name']) ? (string)$data['first_name'] : null,
            lastName: isset($data['last_name']) ? (string)$data['last_name'] : null,
            phone: isset($data['phone']) ? (string)$data['phone'] : null,
        );
    }
}
