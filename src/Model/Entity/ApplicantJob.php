<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ApplicantJob Entity
 *
 * @property int $id
 * @property int $applicant_id
 * @property int $job_id
 * @property string $status
 * @property \Cake\I18n\DateTime|null $applied_at
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Applicant $applicant
 * @property \App\Model\Entity\Job $job
 */
class ApplicantJob extends Entity
{
    protected array $_accessible = [
        'applicant_id' => true,
        'job_id' => true,
        'status' => true,
        'applied_at' => true,
        'created' => true,
        'modified' => true,
    ];
}
