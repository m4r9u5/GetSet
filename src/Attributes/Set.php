<?php

/**
 * This file is part of the GetSet package.
 *
 * @author  Margus Kaidja <margusk@gmail.com>
 * @link    https://github.com/marguskaidja/php-getset
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

declare(strict_types=1);

namespace margusk\Accessors\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS)]
class Set extends Base
{
    protected ?string $mutator;

    public function __construct(?bool $enabled = true, string $mutator = null)
    {
        parent::__construct($enabled);
        $this->mutator = $mutator;
    }

    public function mutator(): string|null
    {
        return $this->mutator;
    }
}
