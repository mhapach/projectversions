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
    private $vcs;

    public function __construct()
    {
        if (!$this->vcs) {
            $svnUrl = env('VCS_PATH') . "/trunk";
            /** @var Svn $vcs */
            $this->vcs = VcsFabric::getInstance('svn', $svnUrl, 'm.khapachev', 'Wlmaga%242');
        }
    }

    public function index()
    {
//        dd($vcs->list());
//
//        $name = 'm.khapachev';
//        $password = 'Wlmaga%242';
////        $versions = svn_ls($svnUrl);
////        dd($svnUrl);
//
//        $auth = base64_encode("$name:$password");
//        $context = stream_context_create([
//            "http" => [
//                "header" => "Authorization: Basic $auth"
//            ]
//        ]);
//        $versions = file_get_contents($svnUrl."/trunk", false, $context );
//        dd($versions);


        return view('projectversions::versions', [
            "vcsLogs" => $this->vcs->logs(),
            "isNew" => $this->vcs->hasNewVersion()
        ]);
    }

    public function checkout(int $revision)
    {
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


    public function isNew()
    {
        $res = $this->vcs->hasNewVersion();
        return response()->json(['result' => $res], 200);
    }

}