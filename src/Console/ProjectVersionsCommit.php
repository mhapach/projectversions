<?php

namespace mhapach\ProjectVersions\Console;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;

/**
 *
 * Коммиты и автоматическое формирование файла файл project.info
 * Содержимое файла будет следующим :
 * Project: название проекта
 * Description: описание сборки
 * Date: дата сборки в формате YYYY-MM-DD hh:mm:ss
 * Version: A.B.C.D-E
 * где
 * • A(versionNumber) – главный номер версии (major version number). (изменение дизайна или полная смена логики работы большией части модулей )
 * При итерационной смене сбрасывает в ноль buildNumber, releaseNumber, releaseType ставит в пустое значение
 * • B(releaseNumber) – номер релиза - увеличивается после публикации на бою предыдущего
 * При итерационной  смене сбрасывает в ноль buildNumber, releaseType ставит в pre-alfa
 * • C(buildNumber) – номер сборки, номер логической итерации по работе над функционалом версии A.B (build number). Увеличивается всякий раз когда отдаем продукт в тестирование.
 * • D – Номер ревизии - обычный инкремент в нашем случае
 * • E(releaseType) – условное обозначение релиза
 *
 * Pre-alpha (pa) – соответствует этапу начала работ над версией. Характеризуется большими изменениями в функционале и большим количеством ошибок. Pre-alpha релизы не покидают отдела разработки ПО.
 * Alpha(a) – соответствует этапу завершения разработки нового функционала. Начиная с alpha версии новый функционал не разрабатывается, а все заявки на новый функционал уходят в план работ по следующей версии. Этап характеризуется высокой активностью по тестированию внутри подразделения разработки ПО и устранению ошибок.
 * Beta (b) – соответствует этапу публичного тестирования. Это первый релиз, который выходит за пределы отдела разработки ПО. На этом этапе принимаются замечания от пользователей по интерфейсу продукта и прочим найденным пользователями ошибкам и неточностям.
 * Release Candidate (rc) – весь функционал реализован и полностью оттестирован, все найденные на предыдущих этапах ошибки исправлены. На этом этапе могут вноситься изменения в документацию и конфигурации продукта.
 * Release (r) - Релиз служит для индикации того, что ПО соответствует всем требованиям качества, и готово для массового распространения. Не определяет способа доставки релиза (сеть или носитель) и служит лишь для индикации того, что качество достаточно для массового распространения.
 *
 * Пример файла product.info:
 * Description: new pre-alfa build
 * Date: 2018-12-27 17:27:41
 * Version: 1.0.1.23455-Pre-Alfa
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
    protected $signature = 'pv:commit {p1?} {p2?} {p3?} {p4?} {p5?}';

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
    /** @var array */
    private $projectInfo;

    /** @var string */
    private $projectInfoFile = 'project.info';

    /** @var string */
    private $vcsPath;
    /** @var string */
    private $vcsType;

    /** @var array */
    private $releaseTypes = [
        '' => '',
        'pa' => 'Pre-alpha',
        'a' => 'Alpha',
        'b' => 'Beta',
        'rc' => 'Release-Candidate',
        'r' => 'Release'
    ];

    /** @var int */
    private $currentVersionNumber = 0;
    /** @var int */
    private $currentReleaseNumber = 0;
    /** @var int */
    private $currentBuildNumber = 0;
    /** @var int  - номер ревизии - наш */
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
        $this->projectInfoFile = base_path() . '/' . $this->projectInfoFile;
        $this->vcsPath = env('VCS_PATH') ?? env('SVN_PATH');
        $this->vcsType = strtolower(config('settings.vcs_type', 'git'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws Exception
     */
    public function handle()
    {
        $this->getParams();
        if (!$this->vcsPath)
            throw new Exception('env variable VCS_PATH is absent or empty');

        if (file_exists($this->projectInfoFile)) {
            $this->parseProjectInfoFile();
        }

        $this->initInputParams();

        if (($this->releaseNumber || $this->currentVersionNumber) && !$this->isMasterBranch())
            if ($this->vcsType == 'svn')
                throw new Exception("Current SVN branch must be trunk if you want to make release tag");
            else
                throw new Exception("Current GIT branch must contain master or main if you want to make release tag");

        $this->projectInfo['Project'] = config('app.name');
        $this->projectInfo['Description'] = (string)$this->description;
        $this->projectInfo['Date'] = (new Carbon())->toDateTimeString();
        $this->projectInfo['Version'] = $this->makeVersion();
        $this->description .= ". Version: {$this->projectInfo['Version']}";

        //Запись в файл product.info тут, инчае не попадет в коммит
        $this->saveProjectInfoFile();

        $this->commit();

        return true;
    }

    /**
     * @return Exception|void
     * @throws Exception
     */
    private function commit()
    {
        $methodName = "{$this->vcsType}_commit";
        if ($this->vcsType == 'svn') {
            $this->svn_commit();
        } else if ($this->vcsType == 'git') {
            $this->git_commit();
        } else
            return new Exception("No such VCS method $methodName");
    }

    /**
     * @throws Exception
     */
    private function git_commit(): void
    {
        /** @var string $currentBranch */
        $currentBranch = null;

        //Проверяем текущая ветка master или main
        $command = 'git branch --show-current';
        exec($command, $rows, $error);
        if (empty($error))
            $currentBranch = trim(current($rows));

        if (!$currentBranch)
            throw new Exception("Current branch is empty");

        if (!preg_match('/master|main/i', $currentBranch))
            throw new Exception("Current branch must contain master or main");

        /* коммит в текущую ветку */
        $command = 'git add ./';
        exec($command, $rows, $error);
        if (!empty($error))
            throw new Exception("Error status $error.\n Execution of  $command error: " . implode("\n", $rows));

        $command = 'git commit -m "' . $this->description . '"';
        exec($command, $rows, $error);
        if (!empty($error))
            throw new Exception("Error status $error.\n Execution of  $command error: " . implode("\n", $rows));

        $command = "git push -u origin $currentBranch";
        exec($command, $rows, $error);
        if (!empty($error))
            throw new Exception("Error status $error.\n Execution of  $command error: " . implode("\n", $rows));

        $command = "git tag {$this->projectInfo['Version']}";
        exec($command, $rows, $error);
        if (!empty($error))
            throw new Exception("Error status $error.\n Execution of  $command error: " . implode("\n", $rows));

        $command = "git push --tags";
        exec($command, $rows, $error);
        if (!empty($error))
            throw new Exception("Error status $error.\n Execution of  $command error: " . implode("\n", $rows));
    }

    private function svn_commit()
    {
        /* коммит в текущую ветку */
        $command = 'svn commit -m "' . $this->description . '"';
        exec($command, $rows, $error);
        dump($rows);

//        // Revision потом понадобиться
//        $this->appendSvnRevision();
//        $command = 'svn commit';
//        exec($command, $rows, $error);

        /* копируем trunk в tugs*/
        if (!$error && $this->releaseNumber) {
            $command = 'svn copy ' . env('SVN_PATH') . '/trunk ' .
                env('SVN_PATH') . '/tags/' . $this->projectInfo['Version'] .
                ' -m "' . $this->description . '"';
            exec($command, $rows);
            dump($rows);
        }
    }

    /**
     * Записываем данные в файл
     */
    private function saveProjectInfoFile(): void
    {
        if (empty($this->projectInfo))
            return;

//        $content = '';
//        foreach ($this->projectInfo as $key => $value) {
//            $content .= "$key=$value\n";
//        }

        file_put_contents($this->projectInfoFile, json_encode($this->projectInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
//        print $content;
    }

    /**
     * Записываем данные в файл
     * @param array $this ->projectInfo
     */
//    private function appendSvnRevision()
//    {
//        file_put_contents($this->projectInfoFile, "Revision=" . $this->getAfterCommitRevisionNumber(), FILE_APPEND);
//    }


    /**
     * парсим текущий файл
     */
    private function parseProjectInfoFile(): void
    {
        if (file_exists($this->projectInfoFile)) {
            try {
                $this->projectInfo = parse_ini_file($this->projectInfoFile, false, INI_SCANNER_RAW);
            } catch (Exception $e) {
            }

            if (!$this->projectInfo)
                $this->projectInfo = json_decode(file_get_contents($this->projectInfoFile), true);

            $version = trim($this->projectInfo['Version'] ?? '');
            if ($version && preg_match('/^(\d+)\.(\d+)\.(\d+)\.(\d+)-?(.*)$/', $version, $matches)) {
                $this->currentVersionNumber = $matches[1] ?? 0;
                $this->currentReleaseNumber = $matches[2] ?? 0;
                $this->currentBuildNumber = $matches[3] ?? 0;
                $this->currentRevisionNumber = $matches[4] ?? 0;
                $this->currentReleaseType = $matches[5] ?? $this->currentReleaseType;
            }
        }
    }

    /**
     * Формируем строку версии
     * @return string
     */
    private function makeVersion(): string
    {
        $res = "$this->currentVersionNumber.$this->currentReleaseNumber.$this->currentBuildNumber.$this->currentRevisionNumber";
        if ($this->currentReleaseType)
            $res .= "-$this->currentReleaseType";
        return $res;
    }

    /**
     * инициализируем параметры
     * @return void
     */
    private function initInputParams()
    {
        if (isset($this->versionNumber))
            $this->currentVersionNumber = $this->versionNumber === '+1' ? ++$this->currentVersionNumber : $this->versionNumber;
        //Если увеличиваем версию то сброс нижестоящих
        if ($this->versionNumber === '+1') {
            $this->releaseNumber = 0;
            $this->buildNumber = 0;
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
    private function getNextReleaseType(string $type) : string
    {
        $types = array_values($this->releaseTypes);
        $i = array_search($type, $types);
        $res = "";
        if ($i !== false)
            $res = ($i != (count($types) - 1)) ? $types[++$i] : $types[$i];
        else
            $res = reset($this->releaseTypes);
        return $res;
    }

    /**
     * Запрашиваем  текущий номер ревизии
     */
    private function setRevisionNumber(): void
    {
        ++$this->currentRevisionNumber;
    }

//    /**
//     * Запрашиваем  номер ревизии ПОсле комита
//     */
//    private function getAfterCommitRevisionNumber()
//    {
//        $afterCommitSvnRevisionNumber = 0;
//        exec("svn info {$this->vcsPath}", $rows);
//        foreach ($rows as $row) if (preg_match('/revision.*?(\d+)/i', $row, $matches)) {
//            $values = explode(":", $row);
//            $afterCommitSvnRevisionNumber = (int)($values[1] ?? 0);
//        }
//        return $afterCommitSvnRevisionNumber;
//    }

    /**
     * Проверяем текущая ветка master или main для git-a или папка trunk Для svn
     * @return bool
     */
    private function isMasterBranch(): bool
    {
        if (config('settings.vcs_type') == 'svn') {
            $command = 'svn info --show-item url';
            exec($command, $rows);
            if (!empty($rows) && preg_match('/trunk$/', current($rows)))
                return true;
        } else {
            $command = 'git branch --show-current';
            exec($command, $rows, $error);
            if (empty($error)) {
                $currentBranch = trim(current($rows));
//                return in_array($currentBranch, ['master', 'main']);
                return preg_match('/master|main/i', $currentBranch);
            }
        }

        return false;
    }

    /**
     * Получаем параметры из консольной коммандной строки:
     * наличие не инициализированного параметра говорит о том, что надо сделать инкременет
     */
    private function getParams(): void
    {
//        $res = [];
        foreach ($this->argument() as $value) {
            if ($value) {
                $params = explode(',', $value);
                foreach ($params as $param) {
                    $keyVal = explode('=', $param);
                    $key = array_shift($keyVal);
                    $val = array_shift($keyVal);
                    if (property_exists($this, $key)) {
                        $this->$key = $val ?? '+1';
//                        $res[] = "$key = {$this->$key}";
                    }
                }
            }
        }
    }
}
