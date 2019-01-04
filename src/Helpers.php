<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System;

use Cradle\Package\System\Fieldset;
use Cradle\Package\System\Schema;

/**
 * Model Fieldset Manager. This was made
 * take advantage of pass-by-ref
 *
 * @vendor   Cradle
 * @package  System
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Helpers
{
    /**
     * @var $fieldsets - Caches Fieldsets
     */
    protected static $fieldsets = [];

    /**
     * @var $schemas - Caches Schemas
     */
    protected static $schemas = [];

    /**
     * @var $templates - Caches templates
     */
    protected static $templates = [];

    /**
     * Returns a cached fieldset
     *
     * @param *string $name
     *
     * @return Fieldset
     */
    public static function getFieldset($name) {
        if (!isset(self::$fieldsets[$name])) {
            self::$fieldsets[$name] = Fieldset::i($name);
        }

        return self::$fieldsets[$name];
    }

    /**
     * Returns a cached schema
     *
     * @param *string $name
     *
     * @return Schema
     */
    public static function getSchema($name) {
        if (!isset(self::$schemas[$name])) {
            self::$schemas[$name] = Schema::i($name);
        }

        return self::$schemas[$name];
    }

    /**
     * Returns a format template
     *
     * @param *string $name
     *
     * @return Schema
     */
    public static function getFormatTemplate($name) {
        $file = sprintf(
            '%s/Model/template/format/%s.html',
            __DIR__,
            $name
        );

        if (!file_exists($file)) {
            return null;
        }

        if (!isset(self::$templates[$name])) {
            self::$templates[$name] = file_get_contents($file);
        }

        return self::$templates[$name];
    }

    /**
     * Returns a format template
     *
     * @param *string $name
     *
     * @return Schema
     */
    public static function fieldNameToDotNotation($name) {
        return trim(
            str_replace(
                '..',
                '.',
                str_replace(
                    ['][', ']', '['],
                    '.',
                    $name
                )
            ),
            '.'
        );
    }
}
