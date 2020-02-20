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
    /**
     * @var string
     */
    public $message = 'The email "%string%" is already subscribed.';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}