<?php
declare(strict_types=1);

namespace App\Error\Exception;

use Cake\Http\Exception\HttpException;

class ValidationException extends HttpException
{
    /**
     * @var array<string, array<string>>
     */
    protected array $details;

    public function __construct(string $message = 'Validation failed', array $details = [], int $code = 422)
    {
        parent::__construct($message, $code);
        $this->details = $details;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}
