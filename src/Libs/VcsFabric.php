<?php
/**
 * Created by PhpStorm.
 * User: m.khapachev
 * Date: 20.05.2020
 * Time: 13:41
 */

namespace mhapach\ProjectVersions\Libs;


use mhapach\ProjectVersions\Libs\Vcs\Git;
use mhapach\ProjectVersions\Libs\Vcs\Svn;

class VcsFabric
{
    /**
     * @param string $type - ['svn', 'git']
     * @param string $url
     * @param string $login
     * @param string $password
     * @return Git|Svn
     */
    public static function getInstance($url, $type = null, $login = null, $password = null)
    {
        if (!$url)
            return null;

        if (!$type && strpos($url, 'git') !== false)
            $type = 'git';
        else if (!$type && strpos($url, 'svn') !== false)
            $type = 'svn';
        elseif (!$type)
            $type = 'svn';

        $obj = null;
        if (strtolower($type) == 'svn') {
            $obj = Svn::create($url, $login ?: '', $password ?: '');
        } else if (strtolower($type) == 'git')
            $obj = Git::create($url, $login ?: '', $password ?: '');

        return $obj;
    }
}