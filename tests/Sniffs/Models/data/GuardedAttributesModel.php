<?php
declare(strict_types=1);

namespace App;

class GuardedAttributesModel
{
    protected $attribute = 'value';

    protected $guarded = [
        'id',
    ];

    public function SomeFunction() {}
}
