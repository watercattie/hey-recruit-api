<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Applicant Entity
 *
 * @property int $id
 * @property int $company_id
 * @property string|null $external_id
 * @property string|null $email
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $phone
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\ApplicantJob[] $applicant_jobs
 */
class Applicant extends Entity
{
    protected array $_accessible = [
        'company_id' => true,
        'external_id' => true,
        'email' => true,
        'first_name' => true,
        'last_name' => true,
        'phone' => true,
        'created' => true,
        'modified' => true,
    ];
}
