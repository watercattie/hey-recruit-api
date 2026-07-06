<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AuditLog Entity
 *
 * @property int $id
 * @property int|null $api_token_id
 * @property string $entity_type
 * @property int|null $entity_id
 * @property string $action
 * @property string $result
 * @property array|null $payload
 * @property \Cake\I18n\DateTime $created
 *
 * @property \App\Model\Entity\ApiToken|null $api_token
 */
class AuditLog extends Entity
{
    protected array $_accessible = [
        'api_token_id' => true,
        'entity_type' => true,
        'entity_id' => true,
        'action' => true,
        'result' => true,
        'payload' => true,
        'created' => true,
    ];
}
