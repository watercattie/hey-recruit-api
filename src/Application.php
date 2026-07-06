<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use App\Middleware\ApiAwareCsrfMiddleware;
use App\Middleware\HostHeaderMiddleware;
use App\Model\Table\ApplicantJobsTable;
use App\Model\Table\ApplicantsTable;
use App\Model\Table\AuditLogsTable;
use App\Model\Table\JobsTable;
use App\Repository\ApplicantJobRepository;
use App\Repository\ApplicantRepository;
use App\Service\ApplicantJobTransformer;
use App\Service\ApplicantJobUpsertService;
use App\Service\AuditLogService;
use App\Validator\BusinessValidator;
use App\Validator\RequestValidator;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Event\EventManagerInterface;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 *
 * @extends \Cake\Http\BaseApplication<\App\Application>
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        // By default, does not allow fallback classes.
        FactoryLocator::add('Table', (new TableLocator())->allowFallbackClass(false));

        // Load the Authentication plugin
        $this->addPlugin('Authentication');
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

            // Validate Host header to prevent Host Header Injection attacks.
            // In production, ensures App.fullBaseUrl is configured and validates
            // the incoming Host header against it.
            ->add(new HostHeaderMiddleware())

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance.
            // See https://github.com/CakeDC/cakephp-cached-routing
            ->add(new RoutingMiddleware($this))

            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/5/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())

            // Add Authentication middleware for API
            ->add(new AuthenticationMiddleware($this))

            // Cross Site Request Forgery (CSRF) Protection Middleware
            // https://book.cakephp.org/5/en/security/csrf.html#cross-site-request-forgery-csrf-middleware
            // Only add CSRF for non-API routes (API uses token auth)
            ->add(new ApiAwareCsrfMiddleware());

        return $middlewareQueue;
    }

    /**
     * Returns a service provider instance for authentication.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request
     * @return \Authentication\AuthenticationServiceInterface
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $service = new AuthenticationService();

        // Configure token authenticator for API with custom identifier
        $service->loadAuthenticator('Authentication.Token', [
            'header' => 'Authorization',
            'tokenPrefix' => 'Bearer',
            'identifier' => [
                'className' => 'Authentication.Token',
                'tokenField' => 'token',
                'dataField' => 'token',
                'resolver' => [
                    'className' => 'Authentication.Orm',
                    'userModel' => 'ApiTokens',
                    'finder' => 'active',
                ],
            ],
        ]);

        return $service;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/5/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
        $locator = FactoryLocator::get('Table');
        /** @var \Cake\ORM\Locator\TableLocator $tableLocator */
        $tableLocator = $locator;

        // Tables (shared instances via TableLocator)
        $container->addShared(ApplicantsTable::class, fn() => $tableLocator->get('Applicants'));
        $container->addShared(ApplicantJobsTable::class, fn() => $tableLocator->get('ApplicantJobs'));
        $container->addShared(JobsTable::class, fn() => $tableLocator->get('Jobs'));
        $container->addShared(AuditLogsTable::class, fn() => $tableLocator->get('AuditLogs'));

        // Repositories
        $container->addShared(ApplicantRepository::class, fn() => new ApplicantRepository(
            $container->get(ApplicantsTable::class),
        ));
        $container->addShared(ApplicantJobRepository::class, fn() => new ApplicantJobRepository(
            $container->get(ApplicantJobsTable::class),
        ));

        // Validators
        $container->addShared(RequestValidator::class, fn() => new RequestValidator());
        $container->addShared(BusinessValidator::class, fn() => new BusinessValidator(
            $container->get(JobsTable::class),
        ));

        // Services
        $container->addShared(AuditLogService::class, fn() => new AuditLogService(
            $container->get(AuditLogsTable::class),
        ));
        $container->addShared(ApplicantJobTransformer::class, fn() => new ApplicantJobTransformer());

        $container->addShared(ApplicantJobUpsertService::class, fn() => new ApplicantJobUpsertService(
            $container->get(ApplicantRepository::class),
            $container->get(ApplicantJobRepository::class),
            $container->get(AuditLogService::class),
        ));
    }

    /**
     * Register custom event listeners here
     *
     * @param \Cake\Event\EventManagerInterface $eventManager
     * @return \Cake\Event\EventManagerInterface
     * @link https://book.cakephp.org/5/en/core-libraries/events.html#registering-listeners
     */
    public function events(EventManagerInterface $eventManager): EventManagerInterface
    {
        // $eventManager->on(new SomeCustomListenerClass());

        return $eventManager;
    }
}
