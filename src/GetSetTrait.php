<?php

declare(strict_types=1);

namespace margusk\GetSet;

use BadMethodCallException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use margusk\GetSet\Attributes\Get;
use margusk\GetSet\Attributes\Set;
use margusk\GetSet\Attributes\Delete;

trait GetSetTrait
{
    protected function loadGetSetConfiguration(): array
    {
        static $conf = [];

        $cl = static::class;

        if (!isset($conf[$cl])) {
            $conf[$cl] = [
                'byCase' => [],
                'byLCase' => []
            ];

            $reflectionClass = new ReflectionClass($cl);

            foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PROTECTED) as $reflectionProperty) {
                $property = $reflectionProperty->getName();
                $lcaseProperty = strtolower($property);

                $conf[$cl]['byCase'][$property] = [
                    'get' => false,
                    'set' => false,
                    'unset' => false,
                    'mutator' => null
                ];

                $conf[$cl]['byLCase'][$lcaseProperty] = $property;

                foreach ($reflectionProperty->getAttributes() as $reflectionAttribute) {
                    switch ($reflectionAttribute->getName()) {
                        case Get::class:
                            $conf[$cl]['byCase'][$property]['get'] = true;
                            break;
                        case Set::class:
                            $conf[$cl]['byCase'][$property]['set'] = true;
                            $mutatorMethod = 'mutator' . $property;

                            if (method_exists($this, $mutatorMethod)) {
                                $conf[$cl]['byCase'][$property]['mutator'] = [$this, $mutatorMethod];
                            }
                            break;
                        case Delete::class:
                            $conf[$cl]['byCase'][$property]['unset'] = true;
                            break;
                    }
                }
            }
        }

        return $conf[$cl];
    }

    public function __set(string $property, mixed $value): void
    {
        $conf = $this->loadGetSetConfiguration();

        if (!isset($conf['byCase'][$property])) {
            throw new InvalidArgumentException(sprintf('tried to set unknown property "%s"', $property));
        }

        $conf = $conf['byCase'][$property];

        if (!$conf['set']) {
            throw new InvalidArgumentException(sprintf('tried to set protected property "%s"', $property));
        }

        if (null !== $conf['mutator']) {
            $value = call_user_func($conf['mutator'], $value);
        }

        $this->{$property} = $value;
    }

    public function __get(string $property): mixed
    {
        $conf = $this->loadGetSetConfiguration();

        if (!isset($conf['byCase'][$property])) {
            throw new InvalidArgumentException(sprintf('tried to read unknown property "%s"', $property));
        }

        $conf = $conf['byCase'][$property];

        if (!$conf['get']) {
            throw new InvalidArgumentException(sprintf('tried to read protected property "%s"', $property));
        }

        return $this->{$property};
    }

    public function __isset(string $property): bool
    {
        $conf = $this->loadGetSetConfiguration();

        if (!isset($conf['byCase'][$property])) {
            throw new InvalidArgumentException(sprintf('tried to query unknown property "%s"', $property));
        }

        $conf = $conf['byCase'][$property];

        if (!$conf['get']) {
            throw new InvalidArgumentException(sprintf('tried to query protected property "%s"', $property));
        }

        return isset($this->{$property});
    }

    public function __unset(string $property): void
    {
        $conf = $this->loadGetSetConfiguration();

        if (!isset($conf['byCase'][$property])) {
            throw new InvalidArgumentException(sprintf('tried to unset unknown property "%s"', $property));
        }

        $conf = $conf['byCase'][$property];

        if (!$conf['unset']) {
            throw new InvalidArgumentException(sprintf('tried to unset protected property "%s"', $property));
        }

        unset($this->{$property});
    }

    public function __call(string $method, array $args): mixed
    {
        $lcaseMethod = strtolower($method);
        $prefix = substr($lcaseMethod, 0, 3);

        if ('set' === $prefix ||
            'get' === $prefix ||
            in_array(($prefix = substr($lcaseMethod, 0, 5)), ['unset', 'isset'])
        ) {
            $conf = $this->loadGetSetConfiguration();

            $property = substr($method, strlen($prefix));
            $property = $conf['byLCase'][strtolower($property)] ?? $property;

            // Call Setter
            if ('s' === $prefix[0]) {
                if (1 !== count($args)) {
                    throw new BadMethodCallException('expecting 1 argument to method %s', $method);
                }
                $this->__set($property, $args[0]);
                return $this;
            // Getter, Setter, Isset
            } else {
                if (0 !== count($args)) {
                    throw new BadMethodCallException('no arguments expected to method %s', $method);
                }

                // Call Getter
                if ('g' === $prefix[0]) {
                    return $this->__get($property);
                // Call Isset
                } elseif ('i' === $prefix[0]) {
                    return $this->__isset($property);
                }

                // Call Unsetter
                $this->__unset($property);
                return $this;
            }
        }

        throw new BadMethodCallException('unknown method %s', $method);
    }
}
