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
            "vcsLogs" => $this->vcs->logs()
        ]);
    }

    public function checkout(int $revision)
    {
        $this->initVcs();
        if (!$this->vcs)
            return response()->json(['result' => false, "message" => "Unauthorized access to vcs"], 401);

        /** @var bool $res */
        $res = $this->vcs->checkout($revision);
        if (!$res)
            return response()->json(['result' => false], 500);
        else
            return response()->json(['result' => true, 'version' => app('version')], 200);
    }

    public function info()
    {
        return response()->json(['result' => app('version')], 200);
    }


    public function new()
    {
        $this->initVcs();
        if (!$this->vcs)
            return response()->json(['result' => false, "message" => "Unauthorized access to vcs"], 401);

        $res = $this->vcs->hasNewVersion();
        return response()->json(['result' => $res], 200);
    }

}