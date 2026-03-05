<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Model\Entity\ApiToken;
use Cake\Event\EventInterface;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\View\JsonView;

/**
 * Base controller for API endpoints.
 *
 * Provides token authentication and company context.
 *
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 */
class ApiController extends AppController
{
    /**
     * @inheritDoc
     */
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    /**
     * Initialize controller.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Authentication.Authentication');

        if ($this->components()->has('FormProtection')) {
            $this->components()->unload('FormProtection');
        }
    }

    /**
     * Before filter - require authentication.
     *
     * @param \Cake\Event\EventInterface $event The event.
     * @return \Cake\Http\Response|null
     */
    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);

        $result = $this->Authentication->getResult();

        if (!$result || !$result->isValid()) {
            throw new UnauthorizedException('Invalid or missing API token');
        }

        $this->updateLastUsedAt();

        return null;
    }

    /**
     * Get the authenticated API token entity.
     *
     * @return \App\Model\Entity\ApiToken
     */
    protected function getApiToken(): ApiToken
    {
        /** @var \App\Model\Entity\ApiToken $token */
        $token = $this->Authentication->getIdentity()->getOriginalData();

        return $token;
    }

    /**
     * Get the company ID from the authenticated token.
     *
     * @return int
     */
    protected function getCompanyId(): int
    {
        return $this->getApiToken()->company_id;
    }

    /**
     * Update last_used_at timestamp on the token.
     *
     * @return void
     */
    protected function updateLastUsedAt(): void
    {
        $token = $this->getApiToken();
        $token->last_used_at = new DateTime();

        $this->fetchTable('ApiTokens')->saveOrFail($token, ['checkRules' => false]);
    }
}
