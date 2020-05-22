<?php
/**
 * Created by PhpStorm.
 * User: m.khapachev
 * Date: 20.05.2020
 * Time: 13:45
 */

namespace mhapach\ProjectVersions\Libs\Vcs;


class Git extends BaseVcs
{
    public static function create(string $url, string $login, string $password)
    {
        $self = new self($url, $login, $password);
        return ($self->auth()) ? $self : null;
    }

    public function auth() {
        return true;
    }
}