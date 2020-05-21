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
     * @param string|null $login
     * @param string|null $password
     * @return Git|Svn
     */
    public static function getInstance(string $type, string $url, string $login = null, string $password = null)
    {
        if (strtolower($type) == 'svn')
            return new Svn($url, $login, $password);
        else if (strtolower($type) == 'git')
            return new Git($url, $login, $password);
    }
}