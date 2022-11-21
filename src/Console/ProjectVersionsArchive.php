<?php

namespace mhapach\ProjectVersions\Console;

use Exception;
use File;
use Illuminate\Console\Command;

/**
 *
 * Коммиты и автоматическое формирование файла файл project.info
 * Class Svn
 * @package App\Console\Commands
 */
class ProjectVersionsArchive extends Command
{
    private Arguments $arguments;

    private Options $options;

    protected $description = 'Archiving current project between two commits or number of last commits ';

    protected $signature = 'pv:archive {from? : commit hash from } {to? : optional commit hash from} {--s|shift= : number of last commits} {--p|path= : path to archive}';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws Exception
     */
    public function handle()
    {
        $this->initArguments();
        $this->initOptions();
        $this->checkAgrAndOptionsConsistency();

        if ($this->arguments->from && $this->arguments->to) {

            $path = rtrim($this->options->path ?: '', '/');

            if ($path && !File::exists($path) && !File::makeDirectory($path, 0755, true))
                throw new Exception("Directory could not be created");

            if ($path && $path != './' && $path != '/')
                $path .= '/';

            $command = "git diff --diff-filter=d --name-only {$this->arguments->from} {$this->arguments->to}";
            exec($command, $rows, $error);
            if (!empty($error))
                throw new Exception("Error status $error.\n Execution of  $command error: " . implode("\n", $rows));

            $command = "git archive -o {$path}{$this->arguments->to}.zip {$this->arguments->to} " . implode(' ', $rows);
            exec($command, $rows, $error);
            if (!empty($error))
                throw new Exception("Error status $error.\n Execution of  $command error: " . implode("\n", $rows));
        }

        return true;
    }

    private function checkAgrAndOptionsConsistency(): void
    {
        if ($this->arguments->isEmpty() && $this->options->isEmpty())
            throw new \InvalidArgumentException("At least on argument or option must be passed");

        if ($this->arguments->to && $this->options->shift)
            throw new \InvalidArgumentException("Option shift is useless when arg to has been passed");

        if (!$this->arguments->from && !$this->options->shift)
            throw new \InvalidArgumentException("Nor agr from nor option shift has been passed");

        if ($this->arguments->from && !$this->arguments->to)
            $this->arguments->to = 'HEAD';

        if ($this->options->shift) {
            $this->arguments->from = 'HEAD~'.$this->options->shift;
            $this->arguments->to = 'HEAD';
        }
    }

    private function initArguments()
    {
        $this->arguments = new Arguments($this->argument());
    }

    private function initOptions()
    {
        $this->options = new Options($this->option());
    }
}

abstract class BaseParams
{
    public function __construct($attributes)
    {
        foreach ($attributes as $name => $value) {
            $name = trim($name);
            if (property_exists($this, $name))
                $this->$name = $value;
        }
    }

    public function isEmpty(): bool
    {
        return empty(array_filter(get_object_vars($this), function ($v) {
            return isset($v);
        }));
    }
}

final class Arguments extends BaseParams
{
    /** @var string|null  - Название коммита от которого начинать */
    public string|null $from;

    /** @var string|null  - Название коммита которым закончить */
    public string|null $to;
}

final class Options extends BaseParams
{
    /** @var string|null - сколько комитов от HEAD отсчитать */
    public string|null $shift;

    /** @var string|null - Путь до архива */
    public string|null $path;
}
