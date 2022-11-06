<?php

/**
 * This file is part of the margusk/accessors package.
 *
 * @author  Margus Kaidja <margusk@gmail.com>
 * @link    https://github.com/marguskaidja/php-accessors
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

declare(strict_types=1);

namespace margusk\Accessors\Template;

use margusk\Accessors\Template\Contract as TemplateContract;

use function implode;
use function preg_match;
use function strtolower;

class Standard implements TemplateContract
{
    public function matchEndpointCandidate(string $method): ?Method
    {
        return $this->matchCalled($method);
    }

    public function matchCalled(string $method): ?Method
    {
        if (
            preg_match(
                '/^(' . implode('|', Method::TYPES) . ')(.*)/i',
                strtolower($method),
                $matches
            )
        ) {
            $methodName = $matches[1];
            $propertyName = $matches[2];

            return new Method(
                Method::TYPES[$methodName],
                $propertyName
            );
        }

        return null;
    }

    public function allowPropertyNameOnly(): bool
    {
        return true;
    }
}