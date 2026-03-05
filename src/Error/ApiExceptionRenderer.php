<?php
declare(strict_types=1);

namespace App\Error;

use App\Error\Exception\ValidationException;
use Cake\Error\Renderer\WebExceptionRenderer;
use Psr\Http\Message\ResponseInterface;

/**
 * Custom exception renderer for API JSON responses.
 */
class ApiExceptionRenderer extends WebExceptionRenderer
{
    /**
     * Render the exception as JSON for API requests.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function render(): ResponseInterface
    {
        $request = $this->controller->getRequest();

        $isApiRequest = str_starts_with($request->getPath(), '/api/');

        if (!$isApiRequest) {
            return parent::render();
        }

        $exception = $this->error;
        $code = $this->getHttpCode($exception);

        $errorCode = match ($code) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            422 => 'VALIDATION_ERROR',
            default => 'ERROR',
        };

        $response = [
            'error' => [
                'code' => $errorCode,
                'message' => $exception->getMessage(),
            ],
        ];

        if ($exception instanceof ValidationException) {
            $details = $exception->getDetails();
            if (!empty($details)) {
                $response['error']['details'] = $details;
            }
        }

        return $this->controller->getResponse()
            ->withStatus($code)
            ->withType('application/json')
            ->withStringBody(json_encode($response, JSON_THROW_ON_ERROR));
    }
}
