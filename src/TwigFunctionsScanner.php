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

use Twig\Environment;
use Twig\Source;
use Twig\Node\Expression\FunctionExpression;

class TwigFunctionsScanner implements FunctionsScannerInterface
{
    private $twig = null;
    private $functions = [];

    public function __construct(Environment $twig, array $functions)
    {
        $this->twig = $twig;
        $this->functions = $functions;
    }

    private function createFunction(FunctionExpression $node, string $filename): ?ParsedFunction
    {
        $name = $node->getAttribute('name');

        if (! in_array($name, $this->functions, true)) {
            return null;
        }

        $line = $node->getTemplateLine();
        $function = new ParsedFunction($name, $filename, $line);

        foreach ($node->getNode('arguments')->getIterator() as $argument) {
            // Some *n*gettext() arguments may not be regular values but expressions.
            $arg = $argument->hasAttribute('value') ? $argument->getAttribute('value') : null;
            $function->addArgument($arg);
        }

        return $function;
    }

    /**
     * Extract twig nodes corresponding to one of the known i18n function calls.
     * @param mixed $token
     */
    private function extractGettextFunctions($token, string $filename, array &$functions): void
    {
        if ($token instanceof FunctionExpression) {
            $function = $this->createFunction($token, $filename);

            if ($function) {
                $functions[] = $function;
            }

            return;
        }

        foreach ($token->getIterator() as $subToken) {
            $this->extractGettextFunctions($subToken, $filename, $functions);
        }
    }

    public function scan(string $code, string $filename): array
    {
        $functions = [];

        $tokens = $this->twig->parse(
            $this->twig->tokenize(new Source($code, $filename))
        );

        $this->extractGettextFunctions($tokens, $filename, $functions);

        return $functions;
    }
}
