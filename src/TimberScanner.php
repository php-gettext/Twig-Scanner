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

use Gettext\Translation;
use Timber\Twig;
use Twig\Environment;

/**
 * Class to scan Twig files and get gettext translations
 */
class TimberScanner extends TwigScanner
{
    protected $functions = [
        '__' => 'text',
        '_e' => 'text',
        '_x' => 'text_context',
        '_ex' => 'text_context',
        '_n' => 'single_plural_number',
        '_nx' => 'single_plural_number_context',
        '_n_noop' => 'single_plural',
        '_nx_noop' => 'single_plural_context',
    ];

    /**
     * Returns a new Twig environment i18n-enabled with Timber.
     */
    public static function createTwig(): Environment
    {
        $twig = parent::createTwig();

        $timber = new Twig();
        $timber->add_timber_functions($twig);
        $timber->add_timber_filters($twig);

        return $twig;
    }

    protected function text(ParsedFunction $function): ?Translation
    {
        list($original, $domain) = array_pad($function->getArguments(), 2, null);

        return $this->addComments(
            $function,
            $this->saveTranslation($domain, null, $original)
        );
    }

    protected function text_context(ParsedFunction $function): ?Translation
    {
        list($original, $context, $domain) = array_pad($function->getArguments(), 3, null);

        return $this->addComments(
            $function,
            $this->saveTranslation($domain, $context, $original)
        );
    }

    protected function single_plural_number(ParsedFunction $function): ?Translation
    {
        list($original, $plural, $number, $domain) = array_pad($function->getArguments(), 4, null);

        return $this->addComments(
            $function,
            $this->saveTranslation($domain, null, $original, $plural)
        );
    }

    protected function single_plural_number_context(ParsedFunction $function): ?Translation
    {
        list($original, $plural, $number, $context, $domain) = array_pad($function->getArguments(), 5, null);

        return $this->addComments(
            $function,
            $this->saveTranslation($domain, $context, $original, $plural)
        );
    }

    protected function single_plural(ParsedFunction $function): ?Translation
    {
        list($original, $plural, $domain) = array_pad($function->getArguments(), 3, null);

        return $this->addComments(
            $function,
            $this->saveTranslation($domain, null, $original, $plural)
        );
    }

    protected function single_plural_context(ParsedFunction $function): ?Translation
    {
        list($original, $plural, $context, $domain) = array_pad($function->getArguments(), 4, null);

        return $this->addComments(
            $function,
            $this->saveTranslation($domain, $context, $original, $plural)
        );
    }
}
