<?php

/**
 * This file is part of the margusk/accessors package.
 *
 * @author  Margus Kaidja <margusk@gmail.com>
 * @link    https://github.com/marguskaidja/php-accessors
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

declare(strict_types=1);

namespace margusk\Accessors;

use margusk\Accessors\Attr\Delete;
use margusk\Accessors\Attr\Get;
use margusk\Accessors\Attr\Immutable;
use margusk\Accessors\Attr\Mutator;
use margusk\Accessors\Attr\Set;
use margusk\Accessors\Exception\InvalidArgumentException;
use ReflectionProperty;

class PropertyConf
{
    /** @var string */
    private string $name;

    /** @var Attributes */
    private Attributes $attr;

    /** @var string|array<int, null|string>|null */
    private string|array|null $mutatorCallback = null;

    /** @var bool */
    private bool $isImmutable = false;

    /** @var bool */
    private bool $isSettable = false;

    /** @var bool */
    private bool $isGettable = false;

    /** @var bool */
    private bool $isUnsettable = false;

    /** @var bool */
    private bool $isPublic;

    /**
     * @param  ReflectionProperty     $rfProperty
     * @param  Attributes             $classAttr
     * @param  array<string, string>  $handlerMethodNames
     */
    public function __construct(
        ReflectionProperty $rfProperty,
        Attributes $classAttr,
        private array $handlerMethodNames
    ) {
        $this->name = $rfProperty->getName();
        $this->isPublic = $rfProperty->isPublic();

        if (false === $this->isPublic) {
            $this->attr = (new Attributes($rfProperty))
                ->mergeToParent($classAttr);

            $this->isImmutable = ($this->attr->get(Immutable::class)?->enabled()) ?? false;
            $this->isGettable = ($this->attr->get(Get::class)?->enabled()) ?? false;
            $this->isSettable = ($this->attr->get(Set::class)?->enabled()) ?? false;
            $this->isUnsettable = ($this->attr->get(Delete::class)?->enabled()) ?? false;

            /** @var ?Mutator $mutator */
            $mutator = $this->attr->get(Mutator::class);
            $mutatorCb = $mutator?->mutator();

            if (is_array($mutatorCb) && 2 === count($mutatorCb)) {
                $mutatorCb = array_map(function (?string $s): ?string {
                    if (null !== $s) {
                        $s = str_replace('%property%', $this->name, $s);
                    }

                    return $s;
                }, $mutatorCb);

                $check = $mutatorCb;

                if (null === $mutatorCb[0]) {
                    $check[0] = $rfProperty->class;
                }

                /**
                 * Check if instance or class method exists in current class.
                 *
                 * It doesn't check if it's actually callable in object context, thus it may generate
                 * exceptions later when actually beeing invoked.
                 *
                 * @var string[] $check
                 */
                if (!method_exists($check[0], $check[1])) {
                    throw InvalidArgumentException::dueInvalidMutatorCallback(
                        $rfProperty->class,
                        $this->name,
                        $check
                    );
                }
            } else {
                if (null !== $mutatorCb && !is_callable($mutatorCb)) {
                    throw InvalidArgumentException::dueInvalidMutatorCallback(
                        $rfProperty->class,
                        $this->name,
                        $mutatorCb
                    );
                }
            }

            $this->mutatorCallback = $mutatorCb;
        } else {
            $this->attr = new Attributes(null);
        }
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function mutator(object $object): ?callable
    {
        if (null === $this->mutatorCallback) {
            return null;
        }

        $mutatorCb = $this->mutatorCallback;

        if (is_array($mutatorCb) && null === $mutatorCb[0]) {
            $mutatorCb[0] = $object;
        }

        /** @var callable|null $mutatorCb */
        return $mutatorCb;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function attr(): Attributes
    {
        return $this->attr;
    }

    public function isImmutable(): bool
    {
        return $this->isImmutable;
    }

    public function isGettable(): bool
    {
        return $this->isGettable;
    }

    public function isSettable(): bool
    {
        return $this->isSettable;
    }

    public function isUnsettable(): bool
    {
        return $this->isUnsettable;
    }

    public function handlerMethodName(string $accessor): ?string
    {
        return $this->handlerMethodNames[$accessor] ?? null;
    }
}
