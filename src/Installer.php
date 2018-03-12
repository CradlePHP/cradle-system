<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
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
class Installer
{
    /**
     * Checks if a path exists
     *
     * @param *string $path
     */
    public static function getNextVersion($module)
    {
        //module root
        $root = cradle('global')->path('module');

        $install = $root . '/' . $module . '/install';

        //if there is no install
        if(!is_dir($install)) {
            return '0.0.1';
        }

        //collect and organize all the versions
        $versions = [];
        $files = scandir($install, 0);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || is_dir($install . '/' . $file)) {
                continue;
            }

            //get extension
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            if ($extension !== 'php'
                && $extension !== 'sh'
                && $extension !== 'sql'
            ) {
                continue;
            }

            //get base as version
            $version = pathinfo($file, PATHINFO_FILENAME);

            //validate version
            if (!(version_compare($version, '0.0.1', '>=') >= 0)) {
                continue;
            }

            $versions[] = $version;
        }

        if(empty($versions)) {
            return '0.0.1';
        }

        //sort versions
        usort($versions, 'version_compare');

        $current = array_pop($versions);
        $revisions = explode('.', $current);
        $revisions = array_reverse($revisions);

        $found = false;
        foreach($revisions as $i => $revision) {
            if(!is_numeric($revision)) {
                continue;
            }

            $revisions[$i]++;
            $found = true;
            break;
        }

        if(!$found) {
            return $current . '.1';
        }

        $revisions = array_reverse($revisions);
        return implode('.', $revisions);
    }

    /**
     * Performs an install
     *
     * @param *string $path
     * @param string  $current
     * @param Closure $callback
     *
     * @return string The current version
     */
    public static function install($path, $current = '0.0.0', Closure $callback = null)
    {
        if(is_null($callback)) {
            $callback = function() {};
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

            if ($extension !== 'php'
                && $extension !== 'sh'
                && $extension !== 'sql'
            ) {
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

            if (call_user_func($callback, $version) === false) {
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
