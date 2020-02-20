<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class UniqueEmail
 * @package App\Validator\Constraints
 * @Annotation
 */
class UniqueEmail extends Constraint
{
    public $message = 'The email "%string%" is already subscribed.';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}