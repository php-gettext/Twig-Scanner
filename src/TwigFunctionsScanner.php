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
use Twig_Node_Expression_Function;

class TwigFunctionsScanner implements FunctionsScannerInterface
{
    private $twig = null;
    private $functions = [];

    public function __construct(Environment $twig, array $functions)
    {
        $this->twig = $twig;
        $this->functions = $functions;
    }

    private function createFunction(Twig_Node_Expression_Function $node, string $filename): ?ParsedFunction
    {
        $name = $node->getAttribute('name');

        if (in_array($name, $this->functions)) {
            return null;
        }

        $line = $value->getTemplateLine();
        $function = new ParsedFunction($name, $filename, $line);

        foreach ($value->getNode('arguments')->getIterator() as $argument) {
            $function->addArgument($argument->getAttribute('value'));
        }

        return $function;
    }

    /**
     * Extract twig nodes corresponding to one of the known i18n function calls.
     * @param mixed $token
     */
    private function extractGettextFunctions($token, string $filename, array &$functions): void
    {
        if ($token instanceof Twig_Node_Expression_Function) {
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
        $function = [];

        $tokens = $this->twig->parse(
            $this->twig->tokenize(new Source($code, $filename))
        );

        foreach ($tokens as $token) {
            $this->extractFunctions($token, $filename, $functions);
        }

        return $functions;
    }
}
