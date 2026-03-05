<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ApiToken Entity
 *
 * @property int $id
 * @property int $company_id
 * @property string $token
 * @property string $name
 * @property bool $is_active
 * @property \Cake\I18n\DateTime|null $last_used_at
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\AuditLog[] $audit_logs
 */
class ApiToken extends Entity
{
    protected array $_accessible = [
        'company_id' => true,
        'token' => true,
        'name' => true,
        'is_active' => true,
        'last_used_at' => true,
        'created' => true,
        'modified' => true,
    ];

    protected array $_hidden = [
        'token',
    ];
}
