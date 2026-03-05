<?php
declare(strict_types=1);

namespace App\Error\Exception;

use Cake\Http\Exception\HttpException;

/**
 * Validation exception with field-level error details.
 */
class ValidationException extends HttpException
{
    /**
     * @var array<string, array<string>>
     */
    protected array $details;

    /**
     * Constructor.
     *
     * @param string $message The error message.
     * @param array<string, array<string>> $details Field-level validation errors.
     * @param int $code HTTP status code (default: 422).
     */
    public function __construct(string $message = 'Validation failed', array $details = [], int $code = 422)
    {
        parent::__construct($message, $code);
        $this->details = $details;
    }

    /**
     * Get validation error details.
     *
     * @return array<string, array<string>>
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
