<?php
/**
 * Created by PhpStorm.
 * User: m.khapachev
 * Date: 20.05.2020
 * Time: 13:45
 */

namespace mhapach\ProjectVersions\Libs\Vcs;


abstract class BaseVcs
{
    /** @var string */
    public $login;
    /** @var string */
    public $password;
    /** @var string */
    public $url;

    protected function __construct(string $url, string $login, string $password)
    {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;
    }

    public static function create(string $url, string $login, string $password){}

    /** Проверка авризован ли пользователь */
    public function auth() {}

    public function logs(){}

    public function checkout(int $revision){}

    public function commit(){}

    /**
     * возвращает последнюю версию если есть
     * @return string
     */
    public function hasNewVersion() : string{}

    public static function version()
    {
        $version = 0;
        if (file_exists(base_path('project.info'))) {

            $projectInfo = parse_ini_file(base_path('project.info'));
            $version = trim($projectInfo['Version'] ?? '');
        }
        return $version;
    }


}