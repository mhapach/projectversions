<?php

namespace  mhapach\ProjectVersions\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 *
Коммиты в SVN и автоматическое формирование файла файл project.info
Содержимое файла будет следующим :
Project: название проекта
Description: описание сборки
Date: дата сборки в формате YYYY-MM-DD hh:mm:ss
Version: A.B.C.D-E
где
• A(versionNumber) – главный номер версии (major version number). (изменение дизайна или полная смена логики работы большией части модулей )
При итерационной смене сбрасывает в ноль buildNumber, releaseNumber, releaseType ставит в pre-alfa
• B(releaseNumber) – номер релиза - увеличивается после публикации на бою предыдущего
При итерационной  смене сбрасывает в ноль buildNumber, releaseType ставит в pre-alfa
• C(buildNumber) – номер сборки, номер логической итерации по работе над функционалом версии A.B (build number). Увеличивается всякий раз когда отдаем продукт в тестирование.
• D – Номер SVN ревизии
• E(releaseType) – условное обозначение релиза

Pre-alpha (pa) – соответствует этапу начала работ над версией. Характеризуется большими изменениями в функционале и большим количеством ошибок. Pre-alpha релизы не покидают отдела разработки ПО.
Alpha(a) – соответствует этапу завершения разработки нового функционала. Начиная с alpha версии новый функционал не разрабатывается, а все заявки на новый функционал уходят в план работ по следующей версии. Этап характеризуется высокой активностью по тестированию внутри подразделения разработки ПО и устранению ошибок.
Beta (b) – соответствует этапу публичного тестирования. Это первый релиз, который выходит за пределы отдела разработки ПО. На этом этапе принимаются замечания от пользователей по интерфейсу продукта и прочим найденным пользователями ошибкам и неточностям.
Release Candidate (rc) – весь функционал реализован и полностью оттестирован, все найденные на предыдущих этапах ошибки исправлены. На этом этапе могут вноситься изменения в документацию и конфигурации продукта.
Release (r) - Релиз служит для индикации того, что ПО соответствует всем требованиям качества, и готово для массового распространения. Не определяет способа доставки релиза (сеть или носитель) и служит лишь для индикации того, что качество достаточно для массового распространения.

Пример файла product.info:
Project: erlvi
Description: new pre-alfa build of erlvi project
Date: 2018-12-27 17:27:41
Version: 1.0.1.23455-Pre-Alfa

 *
 * Примеры использования(внимание все примеры запускать только из корневой папки проекта):
 * Пример 1 (исходная версия 1.1.2.12345-Beta)
 *   Вызов: php artisan svn versionNumber (тоже самое что и php artisan svn versionNumber=+1)
 *   Важно: находится в ветке trunk
 *   Описание: в этом случае изменится(инкрементируется номер версии) и сбросятся releaseNumber=0, buildNumber=0, releaseType=Pre-Alfa
 *             так же автоматически сформируется description вида "New build commit. Version: 2.0.0.12346-Pre-Alfa"
 *             будет сделан svn commit
 *             ветка trunk будет скопирована в папку tags c текущим номером версии 2.0.0.1-Pre-Alfa
 *
 * Пример 2 (исходная версия 1.1.2.12345-Beta)
 *   Вызов: php artisan svn versionNumber=3
 *   Важно: находится в ветке trunk
 *   Описание: в этом случае номер версии станет равный 3 (НЕ инкрементируется ) и остальные параметры версии не сбросятся
 *             так же автоматически сформируется description вида "New build commit. 3.1.2.12346-Beta"
 *             будет сделан svn commit
 *             ветка trunk будет скопирована в папку tags c текущим номером версии 3.1.2.12346-Beta
 *
 * Пример 3 (исходная версия 1.1.2.12345-Beta)
 *   Вызов: php artisan svn releaseNumber (тоже самое что и php artisan svn releaseNumber=+1)
 *   Важно: находится в ветке trunk
 *   Описание: в этом случае изменится(инкрементируется номер релиза) и сбросятся buildNumber=0, releaseType=Pre-Alfa
 *             так же автоматически сформируется description вида "New build commit. Version: 1.2.0.1-Pre-Alfa"
 *             сбросится номер ревизии
 *             будет сделан svn commit
 *             ветка trunk будет скопирована в папку tags c текущим номером версии 1.2.0.1-Pre-Alfa
 *
 * Пример 4 (исходная версия 1.1.2.12345-Beta)
 *   Вызов: php artisan svn buildNumber (тоже самое что и php artisan svn buildNumber=+1)
 *   Описание: в этом случае изменится(инкрементируется номер билда) сбросится revisionNumber = 0 и все остальное останется прежним
 *             так же автоматически сформируется description вида "New build commit. Version: 1.1.3.1-Beta*
 *             и будет сделан svn commit
 *
 * Пример 5 (исходная версия 1.1.2.12345-Beta)
 *   Вызов: php artisan svn releaseType (тоже самое что и php artisan svn releaseType=+1)
 *   Описание: в этом случае изменится(инкрементируется тип релиза) все остальное останется прежним
 *             так же автоматически сформируется description вида "New build commit. Version: 1.1.2.12346-Release Candidate*
 *             и будет сделан svn commit
 *
 * Пример 6 (исходная версия 1.1.2.12345-Beta)
 *   Вызов: php artisan svn releaseType="Release"
 *   Описание: в этом случае тип релиза станет Release Все остальное остентся прежним
 *             так же автоматически сформируется description вида "New build commit. Version: 1.1.2.12346-Release*
 *             и будет сделан svn commit
 *
 * Class Svn
 * @package App\Console\Commands
 */
class ProjectVersionsCommit extends Command
{
    /**
     * параметры могут быть вида versionNumber=+1,releaseNumber=3,buildNumber,releaseType,description
     *
     * @var string - versionNumber=+1,releaseNumber=2,buildNumber=3,releaseType="Beta",description="Завтра жить мы будем лучше чем вчера"
     */
    protected $signature = 'commit {p1?} {p2?} {p3?} {p4?} {p5?}';

    /** параметры консольного скрипта */
    /** @var string | null */
    protected $versionNumber;
    /** @var string | null */
    protected $releaseNumber;
    /** @var string | null */
    protected $buildNumber;
    /** @var string */
    protected $releaseType;
    /** @var string */
    protected $description = 'New build commit';

    /** Служебные */
    /** @var string */
    private $projectInfoFile = 'project.info';
    /** @var string  */
    private $svnPath = '';
    /** @var array  */
    private $releaseTypes = [
        'pa' => 'Pre-alpha',
        'a' => 'Alpha',
        'b' => 'Beta',
        'rc' => 'Release Candidate',
        'r' => 'Release'
    ];

    /** @var int */
    private $currentVersionNumber = 0;
    /** @var int*/
    private $currentReleaseNumber = 0;
    /** @var int */
    private $currentBuildNumber = 0;
    /** @var int  - номер ревизии - наш*/
    private $currentRevisionNumber = 0;
    /** @var string */
    private $currentReleaseType = 'Pre-alpha';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->projectInfoFile = base_path().'/'.$this->projectInfoFile;
        $this->svnPath = env('VCS_PATH') ?? env('SVN_PATH');
        if (!$this->svnPath)
            die("VCS_PATH in .env file is empty or absent");
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $this->getParams();
        if (!$this->svnPath)
            throw new \Exception('env variable SVN_PATH is absent or empty');

        if (file_exists($this->projectInfoFile)) {
            $this->parseProjectInfoFile();
        }

        $this->initInputParams();

        if(($this->releaseNumber || $this->currentVersionNumber) && !$this->isTrunkBranch())
            throw new \Exception("Current branch must be trunk if you want to make release tag");

        $projectInfo['Project'] = config('app.name');
        $projectInfo['Description'] = (string)$this->description;
        $projectInfo['Date'] = (new Carbon())->toDateTimeString();
        $projectInfo['Version'] = $this->makeVersion();
        $this->description .= ". Version: {$projectInfo['Version']}";

        //Запись в файл producn.info  тут, инчае не попадет в коммит
        $this->saveProjectInfoFile($projectInfo);

        /* коммит в текущую ветку */
        $command = 'svn commit -m "'.$this->description.'"';
        exec($command, $rows, $error);
        dump($rows);

//        // Revision потом понадобиться
//        $this->appendSvnRevision();
//        $command = 'svn commit';
//        exec($command, $rows, $error);

        /* копируем trunk в tugs*/
        if(!$error && $this->releaseNumber) {
            $command = 'svn copy ' . env('SVN_PATH') . '/trunk ' .
                env('SVN_PATH') . '/tags/' . $projectInfo['Version'].
                ' -m "' . $this->description . '"';
            exec($command, $rows);
            dump($rows);
        }

    }

    /**
     * Записываем данные в файл
     * @param array $projectInfo
     */
    private function saveProjectInfoFile(array $projectInfo)
    {
        if (empty($projectInfo))
            return;

        $content = '';
        foreach($projectInfo as $key => $value) {
            $content.="$key=$value\n";
        }
        file_put_contents($this->projectInfoFile, $content);
//        print $content;
    }

    /**
     * Записываем данные в файл
     * @param array $projectInfo
     */
    private function appendSvnRevision()
    {
        file_put_contents($this->projectInfoFile, "Revision=".$this->getAfterCommitRevisionNumber(), FILE_APPEND);
    }


    /**
     * парсим текущий файл
     */
    private function parseProjectInfoFile() {
        $projectInfo = parse_ini_file($this->projectInfoFile);
        $version = trim($projectInfo['Version'] ?? '');
        if ($version && preg_match('/^(\d+)\.(\d+)\.(\d+)\.(\d+)-(.*)$/', $version, $matches)) {
            $this->currentVersionNumber = $matches[1] ?? 0;
            $this->currentReleaseNumber = $matches[2] ?? 0;
            $this->currentBuildNumber = $matches[3] ?? 0;
            $this->currentRevisionNumber = $matches[4] ?? 0;
            $this->currentReleaseType = $matches[5] ?? $this->currentReleaseType;
        }
    }

    /**
     * Формируем строку версии
     * @return string
     */
    private function makeVersion() {
        return "{$this->currentVersionNumber}.{$this->currentReleaseNumber}.{$this->currentBuildNumber}.{$this->currentRevisionNumber}-{$this->currentReleaseType}";
    }

    /**
     * инициализируем параметры
     * @return string
     */
    private function initInputParams() {
        if (isset($this->versionNumber))
            $this->currentVersionNumber = $this->versionNumber === '+1' ? ++$this->currentVersionNumber : $this->versionNumber;
        //Если увеличиваем версию то сброс нижестоящих
        if ($this->versionNumber === '+1') {
            $this->releaseNumber = 0;
            $this->buildNumber = 0;
            $this->currentRevisionNumber = 0;
            $this->releaseType = reset($this->releaseTypes);
            $this->currentRevisionNumber = 0;
        }

        if (isset($this->releaseNumber))
            $this->currentReleaseNumber = $this->releaseNumber === '+1' ? ++$this->currentReleaseNumber : $this->releaseNumber;
        //Если увеличиваем версию то сброс нижестоящих
        if ($this->releaseNumber === '+1') {
            $this->buildNumber = 0;
            $this->releaseType = reset($this->releaseTypes);
            $this->currentRevisionNumber = 0;
        }

        if (isset($this->buildNumber)) {
            $this->currentBuildNumber = $this->buildNumber === '+1' ? ++$this->currentBuildNumber : $this->buildNumber;
            $this->currentRevisionNumber = 0;
        }

        if (isset($this->releaseType))
            $this->currentReleaseType = $this->releaseType === '+1' ? $this->getNextReleaseType($this->currentReleaseType) : $this->releaseType;

        $this->setRevisionNumber();
    }

    /**
     * Получаем слудующее значение из типов релизов , если текущее значение в конце то вовращаем его же
     * @param string $type
     * @return mixed
     */
    private function getNextReleaseType(string $type)
    {
        $types = array_values($this->releaseTypes);
        $i = array_search($type, $types);
        if (isset($i))
            return ($i != (count($types) - 1)) ? $types[++$i] : $types[$i];
        else
            return reset($this->releaseTypes);
    }

    /**
     * Запрашиваем  текущий номер ревизии
     */
    private function setRevisionNumber() {
        return ++$this->currentRevisionNumber;
    }

    /**
     * Запрашиваем  номер ревизии ПОсле комита
     */
    private function getAfterCommitRevisionNumber() {
        $afterCommitSvnRevisionNumber = 0;
        exec("svn info {$this->svnPath}", $rows);
        foreach ($rows as $row) if (preg_match('/revision.*?(\d+)/i',$row, $matches)) {
            $values = explode(":", $row);
            $afterCommitSvnRevisionNumber = (int)($values[1] ?? 0);
        }
        return $afterCommitSvnRevisionNumber;
    }

    /**
     * @return bool
     */
    private function isTrunkBranch() {
        $command = 'svn info --show-item url';
        exec($command, $rows);
        if (!empty($rows) && preg_match('/trunk$/', current($rows)))
            return true;

        return false;
    }

    /**
     * Получаем параметры именно потому что наличие не инициализированного параметра говорит о том что надо сделать инкременет
     */
    private function getParams()
    {
        $res = [];
        foreach ($this->argument() as $argument => $value) {
            if ($value) {
                $params = explode(',', $value);
                foreach ($params as $param) {
                    $keyVal = explode('=', $param);
                    $key = array_shift ($keyVal);
                    $val = array_shift ($keyVal);
                    if (property_exists($this, $key)) {
                        $this->$key = isset($val) ? $val : '+1';
                        $res[]="$key = {$this->$key}";
                    }
                }
            }
        }
    }

}
