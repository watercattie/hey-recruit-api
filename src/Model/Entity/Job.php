<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Job Entity
 *
 * @property int $id
 * @property int $company_id
 * @property string|null $external_id
 * @property string $title
 * @property string $status
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\ApplicantJob[] $applicant_jobs
 */
class Job extends Entity
{
    protected array $_accessible = [
        'company_id' => true,
        'external_id' => true,
        'title' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
    ];
}
