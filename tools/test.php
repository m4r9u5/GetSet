<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use margusk\GetSet\Attributes\Get;
use margusk\GetSet\Attributes\Set;
use margusk\GetSet\Attributes\Delete;

class A
{
    use margusk\GetSet\GetSetTrait;

    #[Get,Set,Delete]
    protected string $prop1;

    #[Get]
    protected string $prop2;

    #[Set]
    protected string $prop3;

    protected function mutatorProp1($value)
    {
        return ucfirst($value);
    }
}


$a = new A;

$a->prop1 = 'value1';

$a->setProP1('value1')->setProp3('value3');
//$a->unsetProp1();

echo "Isset: " . $a->issetProp1() . "\n";
echo "Prop1: " . $a->prop1 . "\n";