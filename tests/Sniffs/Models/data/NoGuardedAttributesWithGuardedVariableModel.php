<?php
declare(strict_types=1);

namespace App;

class NoGuardedAttributesWithGuardedVariableModel
{
    protected $attribute = 'value';

    public function SomeFunction() {
        $guarded = 'not this';
    }
}
