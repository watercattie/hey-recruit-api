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

    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Authentication.Authentication');

        if ($this->components()->has('FormProtection')) {
            $this->components()->unload('FormProtection');
        }
    }

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

    protected function getApiToken(): ApiToken
    {
        /** @var \App\Model\Entity\ApiToken $token */
        $token = $this->Authentication->getIdentity()->getOriginalData();

        return $token;
    }

    protected function getCompanyId(): int
    {
        return $this->getApiToken()->company_id;
    }

    protected function updateLastUsedAt(): void
    {
        $token = $this->getApiToken();
        $token->last_used_at = new DateTime();

        $this->fetchTable('ApiTokens')->saveOrFail($token, ['checkRules' => false]);
    }
}
