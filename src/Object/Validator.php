<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System\Object;

use Cradle\Package\System\Schema;
use Cradle\Package\System\Object\Service as ObjectService;

use Cradle\Module\Utility\Validator as UtilityValidator;

use Cradle\Helper\InstanceTrait;

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
    use InstanceTrait;

    /**
     * @var Schema|null $schema
     */
    protected $schema = null;

    /**
     * Adds System Schema
     *
     * @param Schema $schema
     */
    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Returns Create Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public function getCreateErrors(array $data, array $errors = [])
    {
        $object = [];
        $fields = $this->schema->getFields();
        $table = $this->schema->getName();
        foreach ($fields as $field) {
            $name = $table . '_' . $field['name'];
            if (isset($field['validation'])) {
                foreach ($field['validation'] as $validation) {
                    if ($validation['method'] === 'required'
                        && (!isset($data[$name]) || empty($data[$name]))
                    ) {
                        $errors[$name] = $validation['message'];
                    }
                }
            }
        }

        $relations = $this->schema->getRelations();
        foreach ($relations as $relation) {
            if ($relation['many'] != 1) {
                continue;
            }

            if (!isset($data[$relation['primary2']]) || !is_numeric($data[$relation['primary2']])) {
                $errors[$relation['primary2']] = sprintf('%s should be valid', $relation['singular']);
            }
        }

        return self::getOptionalErrors($data, $errors);
    }

    /**
     * Returns Create Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public function getUpdateErrors(array $data, array $errors = [])
    {
        $fields = $this->schema->getFields();
        $table = $this->schema->getName();
        $primary = $this->schema->getPrimaryFieldName();

        if (!isset($data[$primary]) || !is_numeric($data[$primary])) {
            $errors[$primary] = 'Invalid ID';
        }

        foreach ($fields as $name => $field) {
            if (!isset($field['validation'])) {
                continue;
            }

            foreach ($field['validation'] as $validation) {
                if ($validation['method'] === 'required'
                    && isset($data[$name])
                    && empty($data[$name])
                ) {
                    $errors[$name] = $validation['message'];
                }
            }
        }

        return self::getOptionalErrors($data, $errors);

        return $errors;
    }

    /**
     * Returns Optional Errors
     *
     * @param *array $object
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public function getOptionalErrors(array $data, array $errors = [])
    {
        $fields = $this->schema->getFields();
        $table = $this->schema->getName();
        $primary = $this->schema->getPrimaryFieldName();

        foreach ($fields as $field) {
            $name = $table . '_' . $field['name'];
            //if there is no data
            if (!isset($data[$name])) {
                //no need to validate
                continue;
            }

            if (!isset($field['validation']) || !is_array($field['validation'])) {
                continue;
            }

            foreach ($field['validation'] as $validation) {
                switch (true) {
                    case $validation['method'] === 'empty'
                        && empty($data[$name]):
                    case $validation['method'] === 'number'
                        && !is_numeric($data[$name]):
                    case $validation['method'] === 'float'
                        && !preg_match('/^[-+]?(\d*)?\.\d+$/', $data[$name]):
                    case $validation['method'] === 'price'
                        && !preg_match('/^[-+]?(\d*)?\.\d{2}$/', $data[$name]):
                    case $validation['method'] === 'price'
                        && !is_numeric($data[$name]):
                    case $validation['method'] === 'regexp'
                        && !preg_match($validation['parameters'], $data[$name]):
                    case $validation['method'] === 'gt'
                        && !($data[$name] > $validation['parameters']):
                    case $validation['method'] === 'gte'
                        && !($data[$name] >= $validation['parameters']):
                    case $validation['method'] === 'lt'
                        && !($data[$name] < $validation['parameters']):
                    case $validation['method'] === 'lte'
                        && !($data[$name] <= $validation['parameters']):
                    case $validation['method'] === 'char_gt'
                        && !(strlen($data[$name]) > $validation['parameters']):
                    case $validation['method'] === 'char_gte'
                        && !(strlen($data[$name]) >= $validation['parameters']):
                    case $validation['method'] === 'char_lt'
                        && !(strlen($data[$name]) < $validation['parameters']):
                    case $validation['method'] === 'char_lte'
                        && !(strlen($data[$name]) <= $validation['parameters']):
                    case $validation['method'] === 'word_gt'
                        && !(str_word_count($data[$name]) > $validation['parameters']):
                    case $validation['method'] === 'word_gte'
                        && !(str_word_count($data[$name]) >= $validation['parameters']):
                    case $validation['method'] === 'word_lt'
                        && !(str_word_count($data[$name]) < $validation['parameters']):
                    case $validation['method'] === 'word_lte'
                        && !(str_word_count($data[$name]) <= $validation['parameters']):
                    case $validation['method'] === 'date'
                        && !preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/', $data[$name]):
                    case $validation['method'] === 'time'
                        && !preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $data[$name]):
                    case $validation['method'] === 'datetime'
                        && !preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $data[$name]):
                    case $validation['method'] === 'email'
                        && !preg_match('/^(?:(?:(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|\x5c(?=[@,"\[\]'.
                        '\x5c\x00-\x20\x7f-\xff]))(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]'.
                        '\x5c\x00-\x20\x7f-\xff]|\x5c(?=[@,"\[\]\x5c\x00-\x20\x7f-\xff])|\.(?=[^\.])){1,62'.
                        '}(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]\x5c\x00-\x20\x7f-\xff])|'.
                        '[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]{1,2})|"(?:[^"]|(?<=\x5c)"){1,62}")@(?:(?!.{64})'.
                        '(?:[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.?|[a-zA-Z0-9]\.?)+\.(?:xn--[a-zA-Z0-9]'.
                        '+|[a-zA-Z]{2,6})|\[(?:[0-1]?\d?\d|2[0-4]\d|25[0-5])(?:\.(?:[0-1]?\d?\d|2[0-4]\d|25'.
                        '[0-5])){3}\])$/', $data[$name]):
                    case $validation['method'] === 'url'
                        && !preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0'.
                        '-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i', $data[$name]):
                    case $validation['method'] === 'hex'
                        && preg_match('/^[0-9a-fA-F]{6}$/', $data[$name]):
                    case $validation['method'] === 'cc'
                        && !preg_match('/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]'.
                        '{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-'.
                        '5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/', $data[$name]):
                        $errors[$name] = $validation['message'];
                        break;
                    case $validation['method'] === 'one':
                        if (!in_array($data[$name], $validation['parameters'])) {
                            $errors[$name] = $validation['message'];
                        }
                        break;
                    case $validation['method'] === 'unique':
                        $search = Service::get('sql')
                            ->getResource()
                            ->search($table)
                            ->addFilter($name . '= %s', $data[$name]);

                        if (isset($data[$primary])) {
                            $search->addFilter($primary . ' != %s', $data[$primary]);
                        }

                        if ($search->getTotal()) {
                            $errors[$name] = $validation['message'];
                        }
                        break;
                }
            }
        }

        return $errors;
    }
}
