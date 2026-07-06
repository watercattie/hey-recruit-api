<?php
declare(strict_types=1);

namespace App\Enum;

enum ApplicationStatus: string
{
    case New = 'new';
    case Screening = 'screening';
    case Interview = 'interview';
    case Offer = 'offer';
    case Hired = 'hired';
    case Rejected = 'rejected';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    public static function fromString(string $value): self
    {
        return self::from($value);
    }
}
