<?php
/**
 * Created by PhpStorm.
 * User: m.khapachev
 * Date: 20.05.2020
 * Time: 13:45
 */

namespace mhapach\ProjectVersions\Libs\Vcs;


use Illuminate\Support\Facades\Artisan;

abstract class BaseVcs
{
    /** @var string */
    public $login;
    /** @var string */
    public $password;
    /** @var string */
    public $url;
    /** @var string[] */
    public $errors = [];

    protected function __construct(string $url, string $login, string $password)
    {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * @return bool
     */
    public function runMigrations()
    {
        $exitCode = Artisan::call('migrate', array('--path' => 'app/migrations', '--force' => true));
        if ($exitCode)
            return false;
        return true;
    }

    public static function create(string $url, string $login, string $password)
    {
    }

    /** Проверка авризован ли пользователь */
    public function auth()
    {
    }

    public function logs()
    {
    }

    public function checkout(int $revision)
    {
    }

    public function commit()
    {
    }

    /**
     * возвращает последнюю версию если есть
     * @return string
     */
    public function hasNewVersion(): string
    {
    }

    public static function version()
    {
        $projectInfo = self::info();
        return trim($projectInfo['Version'] ?? '');
    }

    /**
     * @return array
     */
    public static function info()
    {
        $projectInfo = [];
//        if (file_exists(base_path('project.info')))
//            $projectInfo = parse_ini_file(base_path('project.info'));

        if (file_exists(base_path('project.info')))
            $projectInfo = json_decode(file_get_contents(base_path('project.info')), true);

        return is_array($projectInfo) ? $projectInfo : [];
    }

}
