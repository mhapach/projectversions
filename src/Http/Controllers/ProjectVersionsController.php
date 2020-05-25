<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 14.05.2020
 * Time: 16:37
 */

namespace mhapach\ProjectVersions\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use mhapach\ProjectVersions\Libs\Vcs\Svn;
use mhapach\ProjectVersions\Libs\VcsFabric;

class ProjectVersionsController extends Controller
{
    /**
     * @var Svn
     */
    private $vcs;

    public function __construct()
    {
    }

    private function initVcs()
    {
        if (!$this->vcs) {
            /** @var Svn $vcs */
            $this->vcs = VcsFabric::getInstance(
                env('VCS_PATH'),
                env('VCS_TYPE'),
                session('projectVersions.login', env('VCS_LOGIN')),
                session('projectVersions.password', env('VCS_PASSWORD'))
            );
        }
    }

    public function index()
    {
        $this->initVcs();
        if (!$this->vcs)
            return redirect(route('project_versions.login'));

        return view('projectversions::versions', [
            "vcsLogs" => $this->vcs->logs(),
            "canUpdate" => $this->isUserAllowedCheckout()
        ]);
    }

    public function update()
    {
        return $this->checkout(0);
    }

    public function checkout(int $revision)
    {
        $this->initVcs();
        if (!$this->vcs)
            return response()->json(['result' => false, "message" => __("Unauthorized access to vcs")], 401);

        if (!$this->isUserAllowedCheckout())
            return response()->json(['result' => false, "message" => __("You are not authorised to make vcs checkout")], 401);

        if (app('project.version') == $this->vcs->getVersionByRevision($revision))
            return response()->json(['result' => false, 'message' => __('Nothing to update')], 200);

        /** @var bool $res */
        $res = $this->vcs->checkout($revision);
        if (!$res)
            return response()->json(['result' => false, 'message' => __('Vcs checkout fault')], 500);

        $res = $this->vcs->runMigrations();
        if (!$res)
            return response()->json(['result' => false, 'message' => __('Migrations fault')], 500);
        else
            return response()->json(['result' => true, 'version' => app('project.version')], 200);
    }

    public function info()
    {
        return response()->json(['result' => app('project.version')], 200);
    }


    public function new()
    {
        $this->initVcs();
        if (!$this->vcs)
            return response()->json(['result' => false, "message" =>  __("Unauthorized access to vcs")], 401);

        $res = $this->vcs->hasNewVersion();
        return response()->json(['result' => $res], 200);
    }

    /**
     * @return bool
     */
    private function isUserAllowedCheckout()
    {
        $users = [];
        if (env('VCS_UPDATE_USERS'))
            $users = explode(",", env('VCS_UPDATE_USERS'));

        if (!empty($users))
            $users = array_map(function ($value) {
                return (int)trim($value);
            }, $users);

        if ((bool)env('VCS_USE_AUTH_MIDDLEWARE') && !empty($users))
            return in_array(Auth::user()->id, $users);
        else
            return true;
    }

}