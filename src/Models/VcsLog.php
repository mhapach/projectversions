<?php
/**
 * Created by PhpStorm.
 * User: m.khapachev
 * Date: 20.05.2020
 * Time: 14:24
 */

namespace mhapach\ProjectVersions\Models;


class VcsLog extends BaseModel
{
    public $revision;
    public $msg;
    public $date;
    public $author;

    public $dates = ["date"];
}