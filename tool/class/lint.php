<?php
/*
 * This file is part of the Retwitter project.
 * Shared from the Rehike project.
 * Copyright (c) 2025-2026 Leymonaide, The Rehike Maintainers.
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at https://mozilla.org/MPL/2.0/.
 */

declare(strict_types=1);
namespace RehikeTool\Linter;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Retwitter\TemplateManager;
use Twig\Template;

require_once "includes/rehike_autoloader.php";

class Linter
{
    private static int $loggerIndentLevel = 0;

    public static function log(string $message): void
    {
        $prefixBuffer = "";
        if (0 != self::$loggerIndentLevel)
        {
            for ($i = 0; $i < self::$loggerIndentLevel * 4; $i++)
            {
                $prefixBuffer .= " ";
            }
            $message = $prefixBuffer . $message;
        }

        // Wrap to 80 characters:
        $tokens = explode(" ", $message);
        $buffer = "";
        $bufferCurLine = "";
        for ($i = 0, $j = count($tokens); $i < $j; $i++)
        {
            // Foresee the length after we add the token:
            if (strlen($bufferCurLine) + strlen($tokens[$i]) > 80)
            {
                $buffer .= $bufferCurLine . "\n$prefixBuffer";
                $bufferCurLine = "";
            }

            $bufferCurLine .= $tokens[$i];

            if ($i != $j)
                $bufferCurLine .= " ";
        }
        $buffer .= $bufferCurLine;
        echo $buffer . PHP_EOL;
    }

    /**
     * Performs runtime linting on the class using the PHP reflection API.
     * 
     * @param class-string $className
     */
    public static function lintClass(string $className): void
    {
        $refCls = new ReflectionClass($className);
        self::log("Class " . $refCls->getName() . ":");
        self::$loggerIndentLevel++;

        foreach ($refCls->getProperties() as $refProp)
        {
            // We want to make sure that properties aren't redefined on
            // subclasses unless their values are changed (even that is a little
            // ugly, but at least there's a legitimate reason to do so)
            $curParent = $refCls->getParentClass();
            while ($curParent)
            {
                try
                {
                    if ($refParentProp = $curParent->getProperty($refProp->getName()))
                    {
                        if ($refProp->getDeclaringClass() != $refParentProp->getDeclaringClass())
                        {
                            self::log("Property redeclared from parent: " . (string)$refProp);

                            // If the value differs, but is not blank, then we'll
                            // accept it (for now)
                            $ourValue = $refProp->getDefaultValue();
                            $theirValue = $refParentProp->getDefaultValue();
                            if (!$refProp->hasDefaultValue())
                            {
                                self::log(
                                    "Property \"" . $refProp->getName() .
                                    "\" is redeclared without a default value in class \"" .
                                    $refCls->getName() . "\" from parent \"" .
                                    $curParent->getName() . "\"."
                                );
                            }
                            else if ($ourValue !== $theirValue)
                            {
                                self::log(
                                    "Property \"" . $refProp->getName() .
                                    "\" is duplicated between class \"" .
                                    $refCls->getName() . "\" and parent \"" .
                                    $curParent->getName() . "\"."
                                );
                            }

                            if ($refProp->getType() != $refParentProp->getType())
                            {
                                self::log(
                                    "Property \"" . $refProp->getName() .
                                    "\", redeclared in class \"" .
                                    $refCls->getName() . "\" from parent \"" .
                                    $curParent->getName() . "\", has a different " .
                                    "type from its parent."
                                );
                            }
                        }
                    }
                }
                catch (ReflectionException $e)
                {
                }

                $curParent = $curParent->getParentClass();
            }

            // We want to ensure that all class members are typed unless they
            // have a doc comment stating "@type resource" or "@type callable"
            // (illegal class member types)
            $refType = null;
            if (($refType = $refProp->getType())
                || self::hasDocCommentType($refProp, 
                    ["resource", "callable"]))
            {
                // This is the good case.
            }
            else
            {
                self::log(
                    "Property \"" . $refProp->getDeclaringClass()->getName() . "::" .
                    $refProp->getName() . "\" lacks a " .
                    "valid type annotation."
                );
            }
        }

        self::$loggerIndentLevel--;
    }

    public static function lintTwigTemplate(string $filePath): void
    {
        self::log("Template \"" . $filePath . "\":");
        self::$loggerIndentLevel++;

        $source = TemplateManager::$twig->getLoader()
            ->getSourceContext($filePath);

        $tokenStream = TemplateManager::$twig->tokenize($source);
        $ast = TemplateManager::$twig->parse($tokenStream);

        foreach ($ast as $node)
        {
            self::lintTwigNode($node);
        }

        self::$loggerIndentLevel--;
    }

    private static function lintTwigNode(\Twig\Node\Node $node): void
    {
        //self::log("Linting node $node");

        if ($node instanceof \Twig\Node\MacroNode)
        {
            // We want the types node to be the very first child node of our
            // macros, but it's fine if it's not.
            $typesNode = null;

            $macroName = $node->getAttribute("name");

            foreach ($node->getNode("body") as $bodyNode)
            foreach ($bodyNode as $i => $childNode)
            {
                //self::log("Checking child node $childNode"); 

                $isFirstSignificantNode = true;
                if ($childNode instanceof \Twig\Node\TypesNode)
                {
                    $typesNode = $childNode;

                    if (!$isFirstSignificantNode)
                    {
                        self::log(
                            "Types node exists for macro \"$macroName\", " .
                            "but is not the first child."
                        );
                    }

                    /**
                     * @var \Twig\Node\Expression\ArrayExpression|null
                     */
                    $macroArguments = $node->getNode("arguments");

                    /**
                     * @var array<string, array{type: string, optional: bool}>
                     */
                    $typeMap = $childNode->getAttribute("mapping");

                    if (!($macroArguments instanceof \Twig\Node\Expression\ArrayExpression))
                    {
                        self::log("\$macroArguments is not ArrayExpression.");
                        continue;
                    }

                    if (!is_array($typeMap))
                    {
                        self::log("\$typeMap is not array.");
                        continue;
                    }

                    foreach ($macroArguments->getKeyValuePairs() as $pair)
                    {
                        /**
                         * @var \Twig\Node\Expression\Variable\LocalVariable
                         */
                        $name = $pair["key"];
                        /**
                         * @var string
                         */
                        $name = $name->getAttribute("name");

                        /**
                         * @var \Twig\Node\Expression\AbstractExpression
                         */
                        $default = $pair["value"];

                        // Look up the type of the node:
                        // NOTE: We have support for templated types, so the
                        // parsing is somewhat nontrivial. This is a temporary
                        // implementation.
                        self::log("Checking type name ".var_export($name, true));
                        $typeMapEntry = $typeMap[$name];
                        if (!is_array($typeMapEntry))
                        {
                            self::log("Type map entry for argument \"$name\" " .
                            "of macro \"$macroName\" should be set, but it's not.");
                            continue;
                        }

                        $typeName = $typeMapEntry["type"];
                        if ($default instanceof \Twig\Node\Expression\ConstantExpression
                            && null !== $default->getAttribute("value"))
                        {
                            $defaultValue = $default->getAttribute("value");

                            $isValidType = match ($typeName)
                            {
                                "int" => \is_int($defaultValue),
                                "float" => \is_float($defaultValue),
                                "bool", "boolean" => \is_bool($defaultValue),
                                "array" => is_array($defaultValue),
                                
                                // PHP anonymous objects can be PHP object types,
                                // but Twig anonymous objects are always PHP
                                // array types.
                                "object" => is_object($defaultValue)
                                    || is_array($defaultValue),
                                "resource" => is_resource($defaultValue),

                                // We'll consider this case to be any user type,
                                // which should not support any default value
                                // other than null, since they cannot be
                                // constructed by the Twig environment.
                                default => false,
                            };

                            if (!$isValidType)
                            {
                                self::log(
                                    "Argument \"$name\" of macro \"$macroName\" " .
                                    "has an illegal default value \"$defaultValue\", expected " .
                                    "$typeName."
                                );
                            }
                        }
                    }
                }
                else if ($childNode instanceof \Twig\Node\TextNode)
                {
                    $text = $childNode->getAttribute("data");
                    if (empty($text))
                    {
                    }
                    else
                    {
                        $isFirstSignificantNode = false;
                    }
                }
                else
                {
                    $isFirstSignificantNode = false;
                }
            }

            if (null === $typesNode)
            {
                self::log("Macro \"$macroName\" does not have a types node.");
            }
        }

        foreach ($node as $childNode)
        {
            self::lintTwigNode($childNode);
        }
    }

    private static function hasDocCommentType(
        ReflectionProperty $refProp,
        ?array $filterList = null,
    ): bool
    {
        $docComment = $refProp->getDocComment();

        if (false === $docComment)
        {
            // No doc comment.
            return false;
        }

        $status = preg_match("/@type\s+(\w)/", $docComment, $matches);
        $type = @$matches[1];
        if (!$status || !$type)
        {
            // No type matched.
            return false;
        }

        if (null === $filterList)
        {
            // At this point, we know we have a return. If we're not filtering,
            // then we're good to go.
            return true;
        }
        else
        {
            foreach ($filterList as $filter)
            {
                if ($type == $filter)
                {
                    return true;
                }
            }
        }

        // We'll hit this point if we have a filter list set and didn't match
        // any of the filter values.
        return false;
    }
}