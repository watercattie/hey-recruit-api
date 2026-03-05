<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Company Entity
 *
 * @property int $id
 * @property string $name
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\ApiToken[] $api_tokens
 * @property \App\Model\Entity\Applicant[] $applicants
 * @property \App\Model\Entity\Job[] $jobs
 */
class Company extends Entity
{
    protected array $_accessible = [
        'name' => true,
        'created' => true,
        'modified' => true,
    ];
}
