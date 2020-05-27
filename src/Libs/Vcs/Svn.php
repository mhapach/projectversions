<?php
/**
 * Created by PhpStorm.
 * User: m.khapachev
 * Date: 20.05.2020
 * Time: 13:44
 */

namespace mhapach\ProjectVersions\Libs\Vcs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use mhapach\ProjectVersions\Models\VcsLog;

class Svn extends BaseVcs
{
    static $instance = null;

    public static function create(string $url, $login, string $password)
    {
        $self = new self($url, $login, $password);
        return ($self->auth()) ? $self : null;
    }

    /**
     * @param $login
     * @param $password
     * @return bool
     */
    public function auth()
    {
        $svnUrl = $this->url;
        if (strpos($this->url, "/trunk") === false)
            $svnUrl = $svnUrl . "/trunk";

//        dump("{$this->login}:{$this->password}");
        $auth = base64_encode("{$this->login}:{$this->password}");
        $context = stream_context_create([
            "http" => [
                "header" => "Authorization: Basic $auth"
            ]
        ]);

        try {
            $versions = file_get_contents($svnUrl, false, $context);
        } catch (\Exception $e) {
            return false;
        }

        return (bool)$versions;
    }

    /**
     * @return VcsLog[]
     */
    public function logs()
    {
        $svnUrl = $this->url;
        if (strpos($this->url, "/trunk") === false)
            $svnUrl = $svnUrl . "/trunk";

        /* коммит в текущую ветку */
        $command = "svn log --non-interactive -g --xml --username={$this->login} --password={$this->password} "
            . ($this->isSvnCanSearch() ? "--limit=5 --search Version: " : "--limit=10000 ")
            . $svnUrl
            . " 2>&1";
        $res = exec($command, $rows, $error);
        if ($error) {
            $this->errors[] = $res;
            Log::error($res);
            return null;
        }

        $xml = implode("\n", $rows);
//        dd($command, $xml);

        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $array = json_decode($json);

        if (empty($array->logentry))
            return null;

        $res = [];
        foreach ($array->logentry as $log)
            if (
                !empty($log->msg)
                && is_string($log->msg)
                && stripos($log->msg, "version:") !== false
                && isset($log->{'@attributes'})
                && count($res) <= 5
            ) {
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
        $command = "svn checkout --non-interactive --username={$this->login} --password={$this->password} $svnUrl . 2>&1";
        $res = exec($command, $rows, $error);
//dump($command, $res);
        if ($error) {
            $this->errors[] = $res;
            Log::error($res);
            return false;
        }

        $command = "svn revert -R --non-interactive --username={$this->login} --password={$this->password} . 2>&1";
        $res = exec($command, $rows, $error);
//dump($command, $res);
        if ($error) {
            $this->errors[] = $res;
            Log::error($res);
            return false;
        }

        $command = "svn update " . ($revision ? "-r $revision " : '') . "--non-interactive --username={$this->login} --password={$this->password} --accept theirs-full . 2>&1";
        $res = exec($command, $rows, $error);
//dump($command, $res);
        if ($error) {
            $this->errors[] = $res;
            Log::error($res);
            return false;
        }

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
        $command = "svn info --non-interactive --username={$this->login} --password={$this->password} --xml 2>&1";
        $res = exec($command, $rows, $error);
        if ($error) {
            $this->errors[] = $res;
            Log::error($res);
            return null;
        }

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
    public function hasNewVersion(): string
    {
        $logs = $this->logs();
        if (empty($logs))
            return '';

        $res = '';
        if (strpos($logs[0]->msg, app('project.version')) === false) {
            $res = $this->getVersionFromDescription($logs[0]);
        }
        return trim($res);
    }

    /**
     * @param int $revision
     * @return string
     */
    public function getVersionByRevision(int $revision)
    {
        $logs = $this->logs();
        if (empty($logs))
            return '';

        if (!$revision) {
            return $this->getVersionFromDescription($logs[0]);
        }

        foreach ($logs as $log) if ($log->revision == $revision) {
            return $this->getVersionFromDescription($log);
        }
        return '';
    }

    /**
     * @param VcsLog $log
     * @return string
     */
    private function getVersionFromDescription(VcsLog $log)
    {
        if (empty($log->msg) || !is_string($log->msg))
            return '';
        return trim(Str::after($log->msg, 'Version:'));
    }

    /**
     * Checking if subversion version is greater then 18 then svn can search
     * @return bool
     */
    private function isSvnCanSearch()
    {
        $res = exec("svn --version 2>&1", $rows, $errors);
        if ($errors)
            Log::error($res);

        $str = implode("", $rows);
        preg_match("/svn, version (\d+)\.(\d+)/", $str, $matches);
        if (!empty($matches)) {
            $version = (int)($matches[1] ?? 0) . ($matches[2] ?? 0);
            return ($version >= 18);
        }
        return false;
    }
}