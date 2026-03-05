<?php
declare(strict_types=1);

namespace App\Enum;

/**
 * Application status for applicant-job relationships.
 */
enum ApplicationStatus: string
{
    case New = 'new';
    case Screening = 'screening';
    case Interview = 'interview';
    case Offer = 'offer';
    case Hired = 'hired';
    case Rejected = 'rejected';

    /**
     * Get all valid status values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if a string is a valid status.
     *
     * @param string $value The value to check.
     * @return bool
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    /**
     * Create from string or throw.
     *
     * @param string $value The status value.
     * @return self
     * @throws \ValueError If invalid status.
     */
    public static function fromString(string $value): self
    {
        return self::from($value);
    }
}
