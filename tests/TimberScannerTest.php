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

use Gettext\Generator\PoGenerator;
use Gettext\Scanner\TimberScanner;
use Gettext\Translations;
use PHPUnit\Framework\TestCase;

class TimberScannerTest extends TestCase
{
    const INPUT_FILE = './tests/assets/input.html.twig';

    protected function initAndGetTranslations(TimberScanner $scanner, int $countDomains = 1): array
    {
        $scanner->ignoreInvalidFunctions(false);
        $scanner->extractCommentsStartingWith('notes:');
        $this->assertCount($countDomains, $scanner->getTranslations());

        $scanner->scanFile(self::INPUT_FILE);
        return $scanner->getTranslations();
    }

    public function testTwigCodeScanner()
    {
        $scanner = new TimberScanner(Translations::create());
        list('' => $translations) = $this->initAndGetTranslations($scanner);

        $this->assertCount(9, $translations);
        $this->assertCount(0, $translations->getHeaders());

        $file = __DIR__ . '/assets/default-domain.po';
        // Note: This test can fail due to `git config core.autocrlf` converting line endings to CRLF
        // Double check any test error output for `#Warning: Strings contain different line endings!`
        $this->assertSame(
            file_get_contents($file),
            (new PoGenerator())->generateString($translations),
            $file
        );
    }

    public function testTwigCodeScannerOtherDomains()
    {
        $scanner = new TimberScanner(
            Translations::create('text-domain1'),
            Translations::create('text-domain2')
        );

        list('text-domain1' => $tr1, 'text-domain2' => $tr2) = $this->initAndGetTranslations($scanner, 2);

        $this->assertCount(1, $tr1);
        $this->assertCount(1, $tr1->getHeaders());
        $file1 = __DIR__ . '/assets/text-domain1.po';
        // Note: This test can fail due to `git config core.autocrlf` converting line endings to CRLF
        // Double check any test error output for `#Warning: Strings contain different line endings!`
        $this->assertSame(
            file_get_contents($file1),
            (new PoGenerator())->generateString($tr1),
            $file1
        );

        $this->assertCount(2, $tr2);
        $this->assertCount(1, $tr2->getHeaders());
        $file2 = __DIR__ . '/assets/text-domain2.po';
        // Note: This test can fail due to `git config core.autocrlf` converting line endings to CRLF
        // Double check any test error output for `#Warning: Strings contain different line endings!`
        $this->assertSame(
            file_get_contents($file2),
            (new PoGenerator())->generateString($tr2),
            $file2
        );
    }
}
