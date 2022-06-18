<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsValidOwner extends Constraint
{
    public $message = 'Cannot set owner to a different user';

    public $anonymousMessage = 'Cannot set owner unless you are authenticated';
}
