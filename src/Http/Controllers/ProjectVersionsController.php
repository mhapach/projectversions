<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 14.05.2020
 * Time: 16:37
 */

namespace mhapach\ProjectVersions\Http\Controllers;

use Illuminate\Routing\Controller;

class ProjectVersionsController extends Controller {

    public function index() {
        return view('projectversions::versions');
    }

}