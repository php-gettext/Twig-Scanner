<?php

/**
 * Copyright (C) 2018-2020 raphael.droz@gmail.com
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types = 1);

// Workaround https://github.com/timber/timber/issues/1754
namespace {
    if (! function_exists('add_action')) {
        function add_action()
        {
        }
    }
    if (! function_exists('apply_filters')) {
        function apply_filters()
        {
        }
    }
}

namespace Gettext\Scanner {

    use Twig\Loader\ArrayLoader;
    use Twig\Environment;

    /**
     * Class to scan Twig files and get gettext translations
     */
    class TwigScanner extends CodeScanner
    {
        const WP_FUNCTIONS = [
            /**
             * WordPress defaults (Timber)
             * Since each of these function takes an optional domain, it can either direct
             * to the d* variant of the corresponding *gettext function.
             */
            '__'       => 'text_domain', // d?gettext
            '_e'       => 'text_domain', // d?gettext
            '_x'       => 'text_context_domain', // d?pgettext
            '_ex'      => 'text_context_domain', // d?pgettext
            '_n'       => 'single_plural_number_domain', // d?ngettext
            '_nx'      => 'single_plural_number_context_domain', // d?npgettext
            '_n_noop'  => 'single_plural_domain', // d?ngettext
            '_nx_noop' => 'single_plural_context_domain', // d?npgettext
        ];

        protected $twig = null;
        protected $constants = [];

        public function scanString(string $string, string $filename): void
        {
            $functionsScanner = $this->getFunctionsScanner();
            $functions = $functionsScanner->scan($string, $filename);
            foreach ($functions as $function) {
                $this->handleFunction($function);
            }
        }

        protected function handleFunction(ParsedFunction $function)
        {
            $handler = $function->getPrototype();
            if (is_null($handler)) {
                if ($this->ignoreInvalidFunctions) {
                    return false;
                }

                throw new Exception(
                    sprintf(
                        'Invalid null handler function "%s" in %s:%d.',
                        $function->getFilename(),
                        $function->getLine(),
                    )
                );
            }

            $translation = call_user_func([$this, $handler], $function);

            if ($translation) {
                $translation->getReferences()->add($function->getFilename(), $function->getLine());
            }
        }

        public function getFunctionsScanner(): FunctionsScannerInterface
        {
            $twig = $this->twig ?: $this->createTwig(true);
            return new TwigFunctionsScanner($twig, $this->functions, $this->constants);
        }

        public function setFunctions(array $functions): parent
        {
            $this->functions = $functions;
            return $this;
        }

        public function setTimberFlavor(): self
        {
            return $this->setFunctions(self::WP_FUNCTIONS);
        }

        /**
         * Set a Twig instance externally.
         *
         * @param Twig\Environment $twig
         */
        private function setTwig(Environment $twig): self
        {
            $this->twig = $twig;
            return $this;
        }

        /**
         * Returns a new Twig environment i18n-enabled.
         */
        public static function createTwig(bool $withTimber = false) : Environment
        {
            $twig = new Environment(new ArrayLoader(['' => '']));
            if ($withTimber) {
                self::enableTimber($twig);
            }
            return $twig;
        }

        /**
         * Register additional functions recognized by Timber into a Twig instance.
         *
         * @return null
         */
        private static function enableTimber(Environment $twig)
        {
            $timber = new \Timber\Twig();
            $timber->add_timber_functions($twig);
            $timber->add_timber_filters($twig);
        }
    }
};
