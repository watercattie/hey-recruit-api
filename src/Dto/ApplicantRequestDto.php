<?php
declare(strict_types=1);

namespace App\Dto;

/**
 * DTO for applicant data in upsert request.
 */
readonly class ApplicantRequestDto
{
    /**
     * Constructor.
     *
     * @param string|null $externalId External ID for lookup.
     * @param string|null $email Email for lookup/create.
     * @param string|null $firstName First name.
     * @param string|null $lastName Last name.
     * @param string|null $phone Phone number.
     */
    public function __construct(
        public ?string $externalId = null,
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $phone = null,
    ) {
    }

    /**
     * Create from request array.
     *
     * @param array<string, mixed> $data The request data.
     * @return self
     */
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

    /**
     * Check if DTO has valid identifier (external_id or email).
     *
     * @return bool
     */
    public function hasValidIdentifier(): bool
    {
        return $this->externalId !== null || $this->email !== null;
    }
}
