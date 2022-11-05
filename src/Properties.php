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

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

use function is_string;
use function preg_match;
use function strtolower;
use function substr;

class Properties
{
    /** @var Property[] */
    private array $properties = [];

    /** @var Property[] */
    private array $propertiesByLowerCase = [];

    /**
     * @param  ReflectionClass<object>  $rfClass
     * @param  Attributes               $classAttributes
     */
    public function __construct(ReflectionClass $rfClass, Attributes $classAttributes)
    {
        /* Learn from DocBlock comments which properties should be exposed and how (read-only,write-only or both) */
        $docBlockAttributes = $this->parseDocBlock($rfClass);

        /**
         * Collect all manually generated accessor endpoints.
         *
         * @var array<string, array<string, string>> $accessorEndpoints
         */
        $accessorEndpoints = [];

        foreach (
            $rfClass->getMethods(
                ReflectionMethod::IS_PROTECTED | ReflectionMethod::IS_PRIVATE | ReflectionMethod::IS_PUBLIC
            ) as $rfMethod
        ) {
            if (!$rfMethod->isStatic()
                && preg_match(
                    '/^(set|get|isset|unset|with)(.+)/',
                    strtolower($rfMethod->name),
                    $matches
                )
            ) {
                $accessorEndpoints[(string)$matches[2]][(string)$matches[1]] = $rfMethod->name;
            }
        }

        /**
         * Find all class properties.
         *
         * Provide accessor functionality only for private and protected properties.
         *
         * Although accessors for public properties are not provided (because it makes the behaviour inconsistent),
         * we'll need to remember them along with private and protected properties, so in case they are accessed,
         * informative error can be reported.
         */
        foreach (
            $rfClass->getProperties(
                ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PUBLIC
            ) as $rfProperty
        ) {
            $name = $rfProperty->getName();
            $nameLowerCase = strtolower($name);

            if (isset($docBlockAttributes[$name])) {
                $attributes = $docBlockAttributes[$name]->mergeWithParent($classAttributes);
            } else {
                $attributes = $classAttributes;
            }

            $p = new Property(
                $rfProperty,
                $attributes,
                ($accessorEndpoints[$nameLowerCase] ?? [])
            );

            $this->propertiesByLowerCase[$nameLowerCase] = ($this->properties[$name] = $p);
        }
    }

    public function findConf(string $name, bool $caseInsensitiveSearch = false): ?Property
    {
        if ($caseInsensitiveSearch) {
            $propertyConf = $this->propertiesByLowerCase[strtolower($name)] ?? null;
        } else {
            $propertyConf = $this->properties[$name] ?? null;
        }

        return $propertyConf;
    }

    /**
     * @param  ReflectionClass<object>  $rfClass
     *
     * @return array<string, Attributes>
     */
    private function parseDocBlock(ReflectionClass $rfClass): array
    {
        static $docBlockParser = null;
        static $docBlockLexer = null;

        $docComment = $rfClass->getDocComment();

        if (!is_string($docComment)) {
            return [];
        }

        if (null === $docBlockParser) {
            $constExprParser = new ConstExprParser();

            $docBlockParser = new PhpDocParser(
                new TypeParser($constExprParser),
                $constExprParser
            );

            $docBlockLexer = new Lexer();
        }

        $node = $docBlockParser->parse(
            new TokenIterator(
                $docBlockLexer->tokenize($docComment)
            )
        );

        $result = [];

        foreach ($node->children as $childNode) {
            if ($childNode instanceof PhpDocTagNode
                && $childNode->value instanceof PropertyTagValueNode
                && str_starts_with($childNode->value->propertyName, '$')) {

                $attributes = Attributes::fromDocBlock($childNode);

                if (null !== $attributes) {
                    $result[substr($childNode->value->propertyName, 1)] = $attributes;
                }
            }
        }

        return $result;
    }
}