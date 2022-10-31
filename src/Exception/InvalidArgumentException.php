<?php

/**
 * This file is part of the margusk/accessors package.
 *
 * @author  Margus Kaidja <margusk@gmail.com>
 * @link    https://github.com/marguskaidja/php-accessors
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

declare(strict_types=1);

namespace margusk\Accessors\Exception;

final class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * @param  string   $class
     * @param  string   $property
     * @param  string[] $callable
     *
     * @return static
     */
    public static function dueInvalidMutatorCallback(string $class, string $property, array $callable): static
    {
        return new self(
            sprintf(
                'mutator callback "%s" for property "%s::$%s" is not valid',
                implode('::', $callable),
                $class,
                $property
            )
        );
    }

    public static function dueTriedToGetUnknownProperty(string $class, string $property): static
    {
        return new self(
            sprintf('tried to get unknown property "%s::$%s"', $class, $property)
        );
    }

    public static function dueTriedToGetMisconfiguredProperty(string $class, string $property): static
    {
        return new self(
            sprintf('tried to get misconfigured property "%s::$%s" (missing #[Get] attribute?)', $class, $property)
        );
    }

    public static function dueTriedToSetUnknownProperty(string $class, string $property): static
    {
        return new self(
            sprintf('tried to set unknown property "%s::$%s"', $class, $property)
        );
    }

    public static function dueTriedToSetMisconfiguredProperty(string $class, string $property): static
    {
        return new self(
            sprintf('tried to set misconfigured property "%s::$%s" (missing #[Set] attribute?)', $class, $property)
        );
    }

    public static function dueTriedToUnsetUnknownProperty(string $class, string $property): static
    {
        return new self(
            sprintf('tried to unset unknown property "%s::$%s"', $class, $property)
        );
    }

    public static function dueTriedToUnsetMisconfiguredProperty(string $class, string $property): static
    {
        return new self(
            sprintf('tried to unset misconfigured property "%s::$%s" (missing #[Delete] attribute?)', $class, $property)
        );
    }

    public static function dueImmutablePropertyCantBeUnset(string $class, string $property): static
    {
        return new self(
            sprintf('immutable property "%s::$%s" can\'t be unset', $class, $property)
        );
    }

    public static function dueMultiPropertyAccessorCanHaveExactlyOneArgument(string $class, string $method): static
    {
        return new self(
            sprintf(
                'when first argument is array() then there can\'t be more arguments to method %s::%s()',
                $class,
                $method
            )
        );
    }

    public static function dueMethodIsMissingPropertyNameArgument(string $class, string $method): static
    {
        return new self(
            sprintf('missing argument #1 (property name) to method %s::%s()', $class, $method)
        );
    }

    public static function duePropertyNameArgumentMustBeString(string $class, string $method, int $expectedArgIdx): static
    {
        return new self(
            sprintf(
                'expecting string as argument #%u (property name) to method %s::%s()',
                $expectedArgIdx,
                $class,
                $method
            )
        );
    }

    public static function dueMethodIsMissingPropertyValueArgument(string $class, string $method, int $expectedArgIdx): static
    {
        return new self(
            sprintf(
                'missing argument #%u (property value) to method %s::%s()',
                $expectedArgIdx,
                $class,
                $method
            )
        );
    }

    public static function dueMethodHasMoreArgumentsThanExpected(string $class, string $method, int $expectedArgCount): static
    {
        return new self(
            sprintf(
                'expecting exactly %u argument(s) to method %s::%s()',
                $expectedArgCount,
                $class,
                $method
            )
        );
    }
}