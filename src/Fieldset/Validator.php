<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System\Fieldset;

use Cradle\Package\System\Fieldset;
use Cradle\Package\System\Exception;

/**
 * Validator layer
 *
 * @vendor   Cradle
 * @package  System
 * @author   Christan Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Validator
{
    /**
     * Returns Table Create Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getCreateErrors(array $data, array $errors = [])
    {
        if (!isset($data['singular']) || empty($data['singular'])) {
            $errors['singular'] = 'Singular is required';
        }

        if (!isset($data['plural']) || empty($data['plural'])) {
            $errors['plural'] = 'Plural is required';
        }

        if (!isset($data['name']) || empty($data['name'])) {
            $errors['name'] = 'Keyword is required';
        }

        if (!isset($data['fields']) || empty($data['fields'])) {
            $errors['fields'] = 'Fields is required';
        }

        if (isset($data['name'])) {
            $exists = cradle('global')->fieldset($data['name']);

            if (!empty($exists)) {
                $errors['name'] = 'Keyword is already used.';
            }
        }

        return self::getOptionalErrors($data, $errors);
    }

    /**
     * Returns Table Update Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getUpdateErrors(array $data, array $errors = [])
    {
        if (isset($data['singular']) && empty($data['singular'])) {
            $errors['singular'] = 'Singular is required';
        }

        if (isset($data['plural']) && empty($data['plural'])) {
            $errors['plural'] = 'Plural is required';
        }

        if (isset($data['name']) && empty($data['name'])) {
            $errors['name'] = 'Keyword is required';
        }

        if (!isset($data['fields']) || empty($data['fields'])) {
            $errors['fields'] = 'Fields is required';
        }

        return self::getOptionalErrors($data, $errors);
    }

    /**
     * Returns Table Optional Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getOptionalErrors(array $data, array $errors = [])
    {
        //validations
        if (isset($data['name']) && !preg_match('#^[a-zA-Z0-9\-_]+$#', $data['name'])) {
            $errors['name'] = 'Keyword must only have letters, numbers, dashes';
        }

        return $errors;
    }
}
