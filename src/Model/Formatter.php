<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System\Model;

use Cradle\Package\System\Schema;
use Cradle\Helper\InstanceTrait;

/**
 * Formatter layer
 *
 * @vendor   Cradle
 * @package  System
 * @author   Christan Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Formatter
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
     * Returns formatted data
     *
     * @param *array $data
     *
     * @return array
     */
    public function formatData(array $data)
    {
        $fields = $this->schema->getFields();
        $table = $this->schema->getName();

        foreach ($fields as $field) {
            $name = $table . '_' . $field['name'];
            //if there's no data
            if (!isset($data[$name])) {
                //no need to format
                continue;
            }

            switch ($field['field']['type']) {
                case 'file':
                case 'image':
                    //upload files
                    $data[$name] = $this->upload($data[$name]);
                    break;
                case 'files':
                case 'images':
                    //upload files
                    $data[$name] = $this->upload($data[$name]);
                    $data[$name] = json_encode($data[$name]);
                    break;
                case 'tag':
                case 'texts':
                case 'textareas':
                case 'wysiwygs':
                case 'meta':
                case 'checkboxes':
                case 'multirange':
                case 'multiselect':
                case 'multifield':
                    //if it's an array already
                    if(is_array($data[$name]) || is_object($data[$name])) {
                        $data[$name] = json_encode($data[$name]);
                        break;
                    }

                    //if it's a json string
                    if(strpos($data[$name], '{') === 0
                        || strpos($data[$name], '[') === 0
                    )
                    {
                        break;
                    }

                    //it can only be comma separated
                    $data[$name] = explode(',', $data[$name]);
                    $data[$name] = json_encode($data[$name]);
                    break;
                case 'created':
                case 'updated':
                case 'datetime':
                    if (trim($data[$name])) {
                        $data[$name] = date('Y-m-d H:i:s', strtotime($data[$name]));
                    } else {
                        $data[$name] = null;
                    }

                    break;
                case 'date':
                    if (trim($data[$name])) {
                        $data[$name] = date('Y-m-d', strtotime($data[$name]));
                    } else {
                        $data[$name] = null;
                    }

                    break;
                case 'time':
                    if (trim($data[$name])) {
                        $data[$name] = date('H:i:s', strtotime($data[$name]));
                    } else {
                        $data[$name] = null;
                    }

                    break;
                case 'password':
                case 'md5':
                    $data[$name] = md5($data[$name]);
                    break;
                case 'sha1':
                    $data[$name] = sha1($data[$name]);
                    break;
                case 'active':
                case 'checkbox':
                    if ($data[$name]) {
                        $data[$name] = 1;
                    } else {
                        $data[$name] = 0;
                    }
                    break;
                case 'uuid':
                case 'token':
                    $data[$name] = md5(uniqid());
                    break;
                case 'number' || 'small':
                    if (!$data[$name]) {
                        $data[$name] = null;
                    }
            }
        }

        return $data;
    }

    /**
     * File Upload
     *
     * @param *array  $data
     * @param *string $name
     *
     * @return string
     */
    public function upload($data)
    {
        $payload = cradle()->makePayload();
        $payload['request']->setStage('data', $data);

        cradle()->trigger(
            'utility-file-upload',
            $payload['request'],
            $payload['response']
        );

        if(!$payload['response']->hasResults('data')) {
            return $data;
        }

        return $payload['response']->getResults('data');
    }
}
