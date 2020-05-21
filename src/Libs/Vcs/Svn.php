<?php
/**
 * Created by PhpStorm.
 * User: m.khapachev
 * Date: 20.05.2020
 * Time: 13:44
 */

namespace mhapach\ProjectVersions\Libs\Vcs;


use Illuminate\Support\Str;
use mhapach\ProjectVersions\Models\VcsInfo;
use mhapach\ProjectVersions\Models\VcsLog;

class Svn extends BaseVcs
{
    /**
     * @return VcsLog[] | null
     */
    public function logs()
    {
        $svnUrl = $this->url;
        if (strpos($this->url, "/trunk") === false)
            $svnUrl = $svnUrl . "/trunk";

        /* коммит в текущую ветку */
        $command = "svn log -g --xml --limit=5 --username={$this->login} --password={$this->password} --search Version: $svnUrl";
        exec($command, $rows, $error);
        $xml = implode("\n", $rows);
//        dd($command, $xml);

        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $array = json_decode($json);
        if (empty($array->logentry))
            return null;
        $res = null;
        foreach ($array->logentry as $log) {
            if (!is_string($log->msg))
                $log->msg = null;
            $log->revision = $log->{'@attributes'}->revision;
            $res[] = new VcsLog($log);
        }
        return $res;
    }

    /**
     * @param int $revision
     * @return bool
     */
    public function checkout(int $revision)
    {
        $svnUrl = $this->url;
        if (strpos($this->url, "/trunk") === false)
            $svnUrl = $svnUrl . "/trunk";

        if (!$revision) {
            $logs = $this->logs();
            if (empty($logs))
                return false;
            $revision = $logs[0]->revision;
        }

        chdir(base_path());
        /* checkout в текущую ветку */
        $command = "svn checkout --username={$this->login} --password={$this->password} $svnUrl .";
        exec($command, $rows, $error);
        if ($error)
            return false;

        $command = "svn update " . ($revision ? "-r $revision " : '') . "--username={$this->login} --password={$this->password} --accept theirs-full";
        exec($command, $rows, $error);
        if ($error)
            return false;
//        dump($command, $error);
        return true;
    }

    /**
     * @return VcsLog | null
     */
    public function revision_info()
    {
        $svnUrl = $this->url;
        if (strpos($this->url, "/trunk") === false)
            $svnUrl = $svnUrl . "/trunk";

//        $command = "svn info --xml --username={$this->login} --password={$this->password} $svnUrl";
        $command = "svn info --xml";
        exec($command, $rows, $error);
        $xml = implode("\n", $rows);
//        dd($command, $xml);

        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $array = json_decode($json);

        if (empty($array->entry))
            return null;

        $params = $array->entry;
        $params->revision = $array->entry->{'@attributes'}->revision;
        $params->date = $array->entry->commit->date;
        $params->author = $array->entry->commit->author;
        return new VcsLog($params);
    }

    /**
     * возвращает последнюю версию
     * @return string
     */
    public function hasNewVersion() : string
    {
        $logs = $this->logs();
        if (empty($logs))
            return '';

        $res = '';
        if (strpos($logs[0]->msg, app('version')) === false) {
            $res = Str::after($logs[0]->msg, 'Version:');
        }
        return trim($res);
    }
}