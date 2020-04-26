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

/**
 * ToDo: Don't make Gettext\Scanner\ParsedFunction final
 */
class ParsedFunction2 extends ParsedFunction
{
    const ALLOWED_PROTOTYPES = [
        'gettext',
        'dgettext',
        'pgettext',
        'dpgettext',
        'ngettext',
        'dngettext',
        'npgettext',
        'dnpgettext',
        'ngettext',
        'dngettext',
        'npgettext',
        'dnpgettext',
    ];

    private $prototype = null;

    /**
     * In order for TwigScanner to inherit CodeScanner.php, the mappings must be self-contained within
     * the CodeScanner::$functions property.
     *
     * Storing the official gettext function allows TwigFunctionScanner...
     * 1. To keep track of the original function name (not hijacking of the $name property)
     * 2. To avoid hardcoding wp-specific function.
     * 3. To store the prototype in order to obtain their handlers.
     */
    public function setPrototype(?string $prototype)
    {
        if ($prototype && !in_array($prototype, self::ALLOWED_PROTOTYPES, true)) {
            throw new Exception('Not a valid gettext function');
        }
        $this->prototype = $prototype;
        return $this;
    }

    public function getPrototype() : ?string
    {
        return $this->prototype;
    }

    public function hasDomain() : bool
    {
        return $this->prototype && substr($this->prototype, 0, 1) === 'd';
    }

    /**
     * Transform prototype (eg: 'gettext') to its domain-counterpart (eg 'dgettext').
     */
    public function setDomain()
    {
        if ($this->prototype) {
            $this->prototype = 'd' . ltrim($this->prototype, 'd');
        }
        return $this;
    }

    /**
     * The opposite of setDomain()
     */
    public function unsetDomain()
    {
        $this->prototype = ltrim($this->prototype, 'd');
        return $this;
    }
}
