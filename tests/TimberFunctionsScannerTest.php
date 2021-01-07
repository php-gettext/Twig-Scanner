<?php

/**
 * Copyright (C) 2018-2020 raphael.droz@gmail.com
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types = 1);

namespace Gettext\Tests;

use Gettext\Scanner\TimberScanner;
use Gettext\Scanner\TwigFunctionsScanner;
use PHPUnit\Framework\TestCase;

class TimberFunctionsScannerTest extends TestCase
{
    private static $timberFunctions = [
        '__',
        '_e',
        '_x',
        '_ex',
        '_n',
        '_nx',
        '_n_noop',
        '_nx_noop',
    ];

    public function testScanOnEmptyCode()
    {
        $scanner = new TwigFunctionsScanner(
            TimberScanner::createTwig(),
            self::$timberFunctions
        );
        $file = __DIR__ . '/assets/input.html.twig';
        $functions = $scanner->scan('', $file);

        $this->assertSame([], $functions);
    }

    public function cmp($function, $file, $line, $name, $args, $comments = [])
    {
        $this->assertSame($file, $function->getFilename());
        $this->assertSame($line, $function->getLine());
        $this->assertSame($line, $function->getLastLine());
        $this->assertSame($name, $function->getName());
        $this->assertSame($args, $function->getArguments());
        if ($comments) {
            $this->assertCount(count($comments), $function->getComments());
        }
    }

    public function testTwigFunctionsExtractor()
    {
        $scanner = new TwigFunctionsScanner(
            TimberScanner::createTwig(),
            self::$timberFunctions
        );
        $file = __DIR__ . '/assets/input.html.twig';
        $code = file_get_contents($file);
        $functions = $scanner->scan($code, $file);
        $this->assertCount(11, $functions);

        // text 1
        $function = array_shift($functions);
        $this->cmp($function, $file, 2, '__', ['text 1']);

        // text 2
        $function = array_shift($functions);
        $this->cmp($function, $file, 3, '__', ['text 2 with domain', 'text-domain1']);

        // text 3
        $function = array_shift($functions);
        $this->cmp($function, $file, 7, '__', ['text 3 (with parenthesis)']);

        // text 4
        $function = array_shift($functions);
        $this->cmp($function, $file, 8, '_x', ['text 4', 'some context here']);

        // text 5
        $function = array_shift($functions);
        $this->cmp($function, $file, 9, '_x', ['text 5 "with double quotes"', 'some other context', 'text-domain2']);

        // text 6
        $function = array_shift($functions);
        $this->cmp($function, $file, 10, '__', ['text 6 \'with escaped single quotes\'']);

        // text 7
        $function = array_shift($functions);
        $this->cmp($function, $file, 14, '_n', ['text 7 %d foo', 'text 7 %d foos', null, 'text-domain2']);

        // text 8
        $function = array_shift($functions);
        $this->cmp($function, $file, 15, '_nx', ['text 8 %d bar', 'text 8 %d bars', null, 'another context']);

        // text 9
        $function = array_shift($functions);
        $this->cmp($function, $file, 16, '__', ['text 9 "with escaped double quotes"']);

        // text 10
        $function = array_shift($functions);
        $this->cmp($function, $file, 17, '__', ["text 10 'with single quotes'"]);

        // text 11
        $function = array_shift($functions);
        $this->cmp($function, $file, 20, '_n', ['text 11 with plural', 'The plural form', 5]);

        /* ToDo
           $comments = $function->getComments();
           $this->assertSame("notes: This is an actual note for translators.", array_shift($comments));
        */
    }
}
