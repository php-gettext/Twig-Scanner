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

use Timber\Twig;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * Class to scan Twig files and get gettext translations
 */
class TwigScanner extends CodeScanner
{
    protected $twig = null;

    /**
     * Returns a new Twig environment.
     */
    public static function createTwig(): Environment
    {
        return new Environment(new ArrayLoader(['' => '']));
    }

    /**
     * Set a Twig instance externally.
     */
    public function setTwig(Environment $twig): self
    {
        $this->twig = $twig;

        return $this;
    }

    public function getFunctionsScanner(): FunctionsScannerInterface
    {
        $twig = $this->twig ?: self::createTwig();

        return new TwigFunctionsScanner($twig, array_keys($this->functions));
    }
}
