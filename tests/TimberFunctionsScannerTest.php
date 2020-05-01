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
        $this->assertSame('__', $function->getName());
        $this->assertSame(1, $function->countArguments());
        $this->assertSame(['text 1'], $function->getArguments());
        $this->assertSame(2, $function->getLine());
        $this->assertSame(2, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());
        $this->assertCount(0, $function->getComments());

        // text 2
        $function = array_shift($functions);
        $this->assertSame('__', $function->getName());
        $this->assertSame(2, $function->countArguments());
        $this->assertSame(['text-domain1', 'text 2 with domain'], $function->getArguments());
        $this->assertSame(3, $function->getLine());
        $this->assertSame(3, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());
        $this->assertCount(0, $function->getComments());

        // text 3
        $function = array_shift($functions);
        $this->assertSame('__', $function->getName());
        $this->assertSame(1, $function->countArguments());
        $this->assertSame(7, $function->getLine());
        $this->assertSame(7, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());
        $this->assertCount(0, $function->getComments());

        // text 4
        $function = array_shift($functions);
        $this->assertSame('_x', $function->getName());
        $this->assertSame(2, $function->countArguments());
        $this->assertSame(['some context here', 'text 4'], $function->getArguments());
        $this->assertSame(8, $function->getLine());
        $this->assertSame(8, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());
        $this->assertCount(0, $function->getComments());

        // text 5
        $function = array_shift($functions);
        $this->assertSame('_x', $function->getName());
        $this->assertSame(3, $function->countArguments());
        $this->assertSame(
            ['text-domain2', 'some other context', 'text 5 "with double quotes"'],
            $function->getArguments()
        );
        $this->assertSame(9, $function->getLine());
        $this->assertSame(9, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());
        $this->assertCount(0, $function->getComments());

        // text 6
        $function = array_shift($functions);
        $this->assertSame('__', $function->getName());
        $this->assertSame(1, $function->countArguments());
        $this->assertSame(['text 6 \'with escaped single quotes\''], $function->getArguments());
        $this->assertSame(10, $function->getLine());
        $this->assertSame(10, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());
        $this->assertCount(0, $function->getComments());

        // text 7
        $function = array_shift($functions);
        $this->assertSame('_n', $function->getName());
        $this->assertSame(3, $function->countArguments());
        $this->assertSame(['text-domain2', 'text 7 %d foo', 'text 7 %d foos'], $function->getArguments());
        $this->assertSame(14, $function->getLine());
        $this->assertSame(14, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());
        $this->assertCount(0, $function->getComments());

        // text 8
        $function = array_shift($functions);
        $this->assertSame('_nx', $function->getName());
        $this->assertSame(3, $function->countArguments());
        $this->assertSame(['another context', 'text 8 %d bar', 'text 8 %d bars'], $function->getArguments());
        $this->assertSame(15, $function->getLine());
        $this->assertSame(15, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());
        $this->assertCount(0, $function->getComments());

        // text 9
        $function = array_shift($functions);
        $this->assertSame('__', $function->getName());
        $this->assertSame(1, $function->countArguments());
        $this->assertSame(['text 9 "with escaped double quotes"'], $function->getArguments());
        $this->assertSame(16, $function->getLine());
        $this->assertSame(16, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());
        $this->assertCount(0, $function->getComments());

        // text 10
        $function = array_shift($functions);
        $this->assertSame('__', $function->getName());
        $this->assertSame(1, $function->countArguments());
        $this->assertSame(["text 10 'with single quotes'"], $function->getArguments());
        $this->assertSame(17, $function->getLine());
        $this->assertSame(17, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());
        $this->assertCount(0, $function->getComments());

        // text 11
        $function = array_shift($functions);
        $this->assertSame('_n', $function->getName());
        $this->assertSame(2, $function->countArguments());
        $this->assertSame(['text 11 with plural', 'The plural form'], $function->getArguments());
        $this->assertSame(20, $function->getLine());
        $this->assertSame(20, $function->getLastLine());
        $this->assertSame($file, $function->getFilename());
        $this->assertCount(0, $function->getComments());
        /* ToDo
        $comments = $function->getComments();
        $this->assertSame("notes: This is an actual note for translators.", array_shift($comments));
        */
    }
}
