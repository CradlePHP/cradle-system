<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System\Object;

use Cradle\Package\System\Schema;

use Cradle\Module\Utility\File;

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
     * @param *array      $data
     * @param array|false $s3
     * @param string|null $upload
     *
     * @return array
     */
    public function formatData(array $data, $s3 = false, $upload = null)
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
                    //try cdn if enabled
                    $data[$name] = File::base64ToS3($data[$name], $s3);
                    //try being old school
                    $data[$name] = File::base64ToUpload($data[$name], $upload);
                    break;
                case 'files':
                case 'images':
                    //upload files
                    //try cdn if enabled
                    $data[$name] = File::base64ToS3($data[$name], $s3);
                    //try being old school
                    $data[$name] = File::base64ToUpload($data[$name], $upload);
                    $data[$name] = json_encode($data[$name]);
                    break;
                case 'tag':
                case 'meta':
                case 'checkboxes':
                case 'multirange':
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
                    }
                    break;
                case 'date':
                    if (trim($data[$name])) {
                        $data[$name] = date('Y-m-d', strtotime($data[$name]));
                    }
                    break;
                case 'time':
                    if (trim($data[$name])) {
                        $data[$name] = date('H:i:s', strtotime($data[$name]));
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
            }
        }

        return $data;
    }
}
