<?php

/**
 * Copyright (C) 2018-2020 raphael.droz@gmail.com
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types = 1);

namespace Gettext\Scanner;

use Twig\Source;
use Twig\Node\Expression\ConstantExpression as TwigConst;
use Gettext\Scanner\ParsedFunction2 as ParsedFunction;

class TwigFunctionsScanner implements FunctionsScannerInterface
{
    private $twig = null;
    private $functions = [];

    public function __construct($twig, array $functions, array $constants = [])
    {
        $this->twig = $twig;
        $this->functions = $functions;
        $this->constants = $constants;
    }

    private function isGettextFunction($obj)
    {
        return $obj instanceof \Twig_Node_Expression_Function
            && array_key_exists($obj->getAttribute('name'), $this->functions);
    }

 
    private static function domain(array $args, int $num)
    {
        return isset($args[$num]) && $args[$num] instanceof TwigConst ? $args[$num]->getAttribute('value') : false;
    }

    private static function error(array $args, string $str)
    {
        printf(STDERR, $str . PHP_EOL);
        printf(STDERR, print_r($args, true));
    }
    private static function warnOnDomain(array $args, int $num)
    {
        if (isset($args[$num]) && ! ($args[$num] instanceof TwigConst)) {
            self::error($args, 'Domain must be a constant expression');
        }
    }

    private static function errOnPlural(array $args, int $num)
    {
        if (! ($args[$num] instanceof TwigConst)) {
            self::error($args, 'Plural must be a constant expression');
            return true;
        }
        return false;
    }

    private static function errorOnContext(array $args, int $num)
    {
        if (isset($args[$num]) && ! ($args[$num] instanceof TwigConst)) {
            self::error($args, 'Context must be a constant expression');
            return true;
        }
        return false;
    }

    /**
     * Extract twig nodes corresponding to one of the known i18n function calls.
     *
     * @param array $token
     * @param array $constants Unused yet.
     * @return array List of functions arguments/line-number compatible with PhpFunctionsScanner.
     */
    private function extractGettextFunctions($tokens, $constants = [])
    {
        if (is_array($tokens)) {
            $functions = [];
            foreach ($tokens as $v) {
                $functions = array_merge($functions, self::extractGettextFunctions($v));
            }
            return $functions;
        }

        $value = $tokens;
        if ($this->isGettextFunction($value)) {
            $nodeArguments = (array)$value->getNode('arguments')->getIterator();
            $name = $value->getAttribute('name');
            $line = $value->getTemplateLine();

            if (count($nodeArguments) < 1) {
                self::error($nodeArguments, 'gettext function expects needs at least a source argument');
                return [];
            }
            if (! ($nodeArguments[0] instanceof TwigConst)) {
                self::error($nodeArguments, 'Source must be a constant expression');
                return [];
            }

            $func_type = $this->functions[$name];
            switch ($func_type) {
                case 'text_domain':
                    $original = $nodeArguments[0]->getAttribute('value');
                    self::warnOnDomain($nodeArguments, 1);
                    $domain = self::domain($nodeArguments, 1);
                    $data   = [$original, $domain];
                    return [[$name, $line, $data]];

                case 'text_context_domain':
                    $original = $nodeArguments[0]->getAttribute('value');
                    if (self::errorOnContext($nodeArguments, 1)) {
                        return [];
                    }
                    $context = $nodeArguments[1]->getAttribute('value');
                    self::warnOnDomain($nodeArguments, 2);
                    $domain = self::domain($nodeArguments, 2);
                    $data   = [$original, $context, $domain];
                    return [[$name, $line, $data]];

                case 'single_plural_number_domain':
                    if (count($nodeArguments) < 2) {
                        self::err($nodeArguments, "$func_type expects at least two arguments");
                        return [];
                    }
                    $original = $nodeArguments[0]->getAttribute('value');
                    if (self::errOnPlural($nodeArguments, 1)) {
                        return [];
                    }
                    $plural = $nodeArguments[1]->getAttribute('value');
                    self::warnOnDomain($nodeArguments, 3);
                    $domain = self::domain($nodeArguments, 3);
                    $data   = [$original, $plural, 0, $domain];
                    return [[$name, $line, $data]];

                case 'single_plural_number_context_domain':
                    if (count($nodeArguments) < 2) {
                        self::error($nodeArguments, "$func_type expects at least two arguments");
                        return [];
                    }
                    $original = $nodeArguments[0]->getAttribute('value');
                    if (self::errOnPlural($nodeArguments, 1)) {
                        return [];
                    }
                    $plural = $nodeArguments[1]->getAttribute('value');
                    if (self::errorOnContext($nodeArguments, 3)) {
                        return [];
                    }
                    $context = $nodeArguments[3]->getAttribute('value');
                    self::warnOnDomain($nodeArguments, 4);
                    $domain = self::domain($nodeArguments, 4);
                    $data   = [$original, $plural, 0, $context, $domain];
                    return [[$name, $line, $data]];

                case 'single_plural_domain':
                    if (count($nodeArguments) < 2) {
                        self::error($nodeArguments, "$func_type expects at least two arguments");
                        return [];
                    }
                    $original = $nodeArguments[0]->getAttribute('value');
                    if (self::errOnPlural($nodeArguments, 1)) {
                        return [];
                    }
                    $plural = $nodeArguments[1]->getAttribute('value');
                    self::warnOnDomain($nodeArguments, 2);
                    $domain = self::domain($nodeArguments, 2);
                    $data   = [$original, $plural, $domain];
                    return [[$name, $line, $data]];

                case 'single_plural_context_domain':
                    if (count($nodeArguments) < 2) {
                        self::error($nodeArguments, "$func_type expects at least two arguments");
                        return [];
                    }
                    $original = $nodeArguments[0]->getAttribute('value');
                    if (self::errOnPlural($nodeArguments, 1)) {
                        return [];
                    }
                    $plural = $nodeArguments[1]->getAttribute('value');
                    if (self::errorOnContext($nodeArguments, 2)) {
                        return [];
                    }
                    $context = $nodeArguments[2]->getAttribute('value');
                    self::warnOnDomain($nodeArguments, 3);
                    $domain = self::domain($nodeArguments, 3);
                    $data   = [$original, $plural, $context, $domain];
                    return [[$name, $line, $data]];
                default:
                    print('total error');
            }
            return [];
        }

        $functions = [];
        foreach ($tokens->getIterator() as $v) {
            $functions = array_merge($functions, self::extractGettextFunctions($v));
        }
        return $functions;
    }

    // This is bundled as-is from
    // https://github.com/wp-cli/i18n-command/blob/master/src/PhpFunctionsScanner.php#L12
    public function scan(string $code, string $filename): array
    {
        $parsedFunctions = [];
        $gtFuncNames = $this->functions;
        $this->tokens = $this->twig->parse(
            $this->twig->tokenize(
                new Source(
                    $code,
                    $filename
                )
            )
        );

        $foundFuncs = $this->extractGettextFunctions($this->tokens, $this->constants);
        foreach ($foundFuncs as $function) {
            list($name, $line, $args) = $function;

            if (! isset($gtFuncNames[$name])) {
                continue;
            }

            $original = array_shift($args);
            if ((string) $original === '') {
                continue;
            }

            // See https://github.com/php-gettext/Gettext/issues/255 for possible factorization
            $p = $context = $plural = null;
            switch ($gtFuncNames[$name]) {
                case 'text_domain':
                    list($domain) = array_pad($args, 1, null);
                    $p = new ParsedFunction($name, $filename, $line);
                    $p->setPrototype('gettext');
                    if ($domain) {
                        $p->setDomain()->addArgument($domain);
                    }
                    $p->addArgument($original);
                    break;

                case 'text_context_domain':
                    list($context, $domain) = array_pad($args, 2, null);
                    $p = new ParsedFunction($name, $filename, $line);
                    $p->setPrototype('pgettext');
                    if ($domain) {
                        $p->setDomain()->addArgument($domain);
                    }
                    $p->addArgument($context);
                    $p->addArgument($original);
                    break;

                case 'single_plural_number_domain':
                    list($plural, $number, $domain) = array_pad($args, 3, null);
                    $p = new ParsedFunction($name, $filename, $line);
                    $p->setPrototype('ngettext');
                    if ($domain) {
                        $p->setDomain()->addArgument($domain);
                    }
                    $p->addArgument($original);
                    $p->addArgument($plural);
                    break;

                case 'single_plural_number_context_domain':
                    list($plural, $number, $context, $domain) = array_pad($args, 4, null);
                    $p = new ParsedFunction($name, $filename, $line);
                    $p->setPrototype('npgettext');
                    if ($domain) {
                        $p->setDomain()->addArgument($domain);
                    }
                    $p->addArgument($context);
                    $p->addArgument($original);
                    $p->addArgument($plural);
                    break;

                case 'single_plural_domain':
                    list($plural, $domain) = array_pad($args, 2, null);
                    $p = new ParsedFunction($name, $filename, $line);
                    $p->setPrototype('ngettext');
                    if ($domain) {
                        $p->setDomain()->addArgument($domain);
                    }
                    $p->addArgument($original);
                    $p->addArgument($plural);
                    break;

                case 'single_plural_context_domain':
                    list($plural, $context, $domain) = array_pad($args, 3, null);
                    $p = new ParsedFunction($name, $filename, $line);
                    $p->setPrototype('npgettext');
                    if ($domain) {
                        $p->setDomain()->addArgument($domain);
                    }
                    $p->addArgument($context);
                    $p->addArgument($original);
                    $p->addArgument($plural);
                    break;

                default:
                    // Should never happen.
                    fprintf(STDERR, "Internal error: unknown function '%s' for '%s'.\n", $gtFuncNames[$name], $name);
            }

            /**
             * Since Twig does not provide comment as part of tokens AST,
             * it's not possible to extract them.
             */
            if (false && isset($function[3])) {
                foreach ($function[3] as $extractedComment) {
                    $p->addComment($extractedComment);
                }
            }

            $parsedFunctions[] = $p;
        }

        return $parsedFunctions;
    }
}
