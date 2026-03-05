<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * CSRF protection middleware that skips API routes.
 *
 * API routes use token authentication instead of CSRF tokens.
 */
class ApiAwareCsrfMiddleware extends CsrfProtectionMiddleware
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(['httponly' => true]);
    }

    /**
     * Process the request, skipping CSRF for API routes.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The handler.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        // Skip CSRF completely for API routes
        $path = $request->getUri()->getPath();
        if (str_starts_with($path, '/api/')) {
            return $handler->handle($request);
        }

        return parent::process($request, $handler);
    }
}
