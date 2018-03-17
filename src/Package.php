<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System;

use Closure;
use Cradle\Storm\SqlFactory;
use Cradle\Framework\CommandLine;

/**
 * Installer
 *
 * @vendor   Cradle
 * @package  System
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Package
{
    /**
     * Performs an install
     *
     * @param *string      $path
     * @param string       $current
     * @param string|null  $type
     *
     * @return string The current version
     */
    public static function install($name, $current = '0.0.0', $type = null)
    {
        $package = cradle()->register($name)->package($name);
        $path = $package->getPackagePath() . '/install';
        //if there is no install folder
        if (!is_dir($path)) {
            //there's nothing we can do (try composer update first)
            return $current;
        }

        //collect and organize all the versions
        $versions = [];
        $files = scandir($path, 0);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || is_dir($path . '/' . $file)) {
                continue;
            }

            //get extension
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            //valid extensions
            if ($extension !== 'php'
                && $extension !== 'sh'
                && $extension !== 'sql'
            ) {
                continue;
            }

            //only run updates on a following type
            if ($type && $type !== $extension) {
                continue;
            }

            //get base as version
            $version = pathinfo($file, PATHINFO_FILENAME);

            //validate version
            if (!(version_compare($version, '0.0.1', '>=') >= 0)) {
                continue;
            }

            $versions[$version][] = [
                'script' => $path . '/' . $file,
                'mode' => $extension
            ];
        }

        //sort versions
        uksort($versions, 'version_compare');

        //prepare incase
        $database = SqlFactory::load(cradle('global')->service('sql-main'));

        //now run the scripts in order of version
        foreach ($versions as $version => $files) {
            //if 0.0.0 >= 0.0.1
            if (version_compare($current, $version, '>=')) {
                continue;
            }

            //run the scripts
            foreach ($files as $file) {
                switch ($file['mode']) {
                    case 'php':
                        include $file['script'];
                        break;
                    case 'sql':
                        $query = file_get_contents($file['script']);
                        $database->query($query);
                        break;
                    case 'sh':
                        exec($file['script']);
                        break;
                }
            }
        }

        //if 0.0.0 < 0.0.1
        if (version_compare($current, $version, '<')) {
            $current = $version;
        }

        return $current;
    }
}
