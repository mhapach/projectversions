<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 14.05.2020
 * Time: 16:37
 */

namespace mhapach\ProjectVersions\Http\Controllers;

use Illuminate\Routing\Controller;
use mhapach\ProjectVersions\Libs\Vcs\Svn;
use mhapach\ProjectVersions\Libs\VcsFabric;

class LoginController extends Controller
{
    private $vcs;

    public function index()
    {

        return view('projectversions::login');
    }

    public function login()
    {
        $login = request('login', session()->get('projectVersions.login', env('VCS_LOGIN')));
        $password = request('password', session()->get('projectVersions.password', env('VCS_PASSWORD')));
        $vcs = VcsFabric::getInstance(
            env('VCS_PATH'),
            env('VCS_TYPE'),
            $login,
            $password
        );

        if ($vcs) {
            session()->put('projectVersions.login', $login);
            session()->put('projectVersions.password', $password);
            return redirect(route('project_versions.index'));
        } else
            return redirect(route('project_versions.login'))->withErrors([
                'login' => 'Введенные учетные данные не найдены в системе. Проверьте правильность ввода учетных данных.'
            ]);


    }

}