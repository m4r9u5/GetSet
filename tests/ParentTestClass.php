<?php

/**
 * This file is part of the margusk/accessors package.
 *
 * @author  Margus Kaidja <margusk@gmail.com>
 * @link    https://github.com/marguskaidja/php-accessors
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

declare(strict_types=1);

namespace margusk\Accessors\Tests;

use margusk\Accessors\Attributes\Delete;
use margusk\Accessors\Attributes\Get;
use margusk\Accessors\Attributes\Set;
use margusk\Accessors\Accessible;

#[Get, Set, Delete]
class ParentTestClass
{
    use Accessible;

    protected string $parentProperty;

    public static function staticMutateP1($value): string
    {
        return htmlspecialchars($value);
    }

    public function nonStaticMutate($value): string
    {
        return htmlspecialchars($value);
    }
}