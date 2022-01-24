<?php
declare(strict_types=1);

namespace Insolita\ComposerRecovery;

use Throwable;
use function array_keys;
use function array_merge;
use function array_reduce;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function in_array;
use function json_decode;
use function json_encode;
use function rtrim;

final class Runner
{
    private const EXIT_OK = 0;
    private const EXIT_ERROR = 1;

    /**
     * @var string
     */
    private $installedPath;

    /**
     * @var string
     */
    private $lockPath;

    /**
     * @var string
     */
    private $outputPath;

    /**
     * @var array
     */
    private $result = [];

    public function __construct(string $projectPath, string $outputPath)
    {
        $ds = DIRECTORY_SEPARATOR;
        $projectPath = rtrim($projectPath, $ds) . $ds;
        $this->lockPath = $projectPath . 'composer.lock';
        $this->installedPath = $projectPath . 'vendor' . $ds . 'composer' . $ds . 'installed.json';
        $this->outputPath = $outputPath;
    }

    public function run():int
    {
        try {
            if (file_exists($this->lockPath)) {
                $this->debug('Try to recover from ' . $this->lockPath);
                $this->result = $this->resolveByComposerLock($this->lockPath);
            } elseif (file_exists($this->installedPath)) {
                $this->debug('Try to recover from ' . $this->installedPath);
                $this->result = $this->resolveByInstalledJson($this->installedPath);
            } else {
                $this->debug('Can`t found sources for recover');
                return self::EXIT_ERROR;
            }
            $this->saveResult();
        } catch (Throwable $e) {
            $this->debug($e->getMessage());
            return self::EXIT_ERROR;
        }
        return self::EXIT_OK;
    }

    private function resolveByInstalledJson(string $installedPath):array
    {
        $data = $this->loadJson($installedPath);
        if (empty($data)) {
            return [];
        }
        $packages = $data['packages'] ?? $data;
        $packagesDev = $data['dev-package-names'] ?? [];

        $deps = array_reduce($packages, [$this, 'collectRequirements'], []);
        $result['require'] = $this->buildMap($packages, $deps);

        foreach ($packagesDev as $devPackageName) {
            if(isset($result['require'][$devPackageName])) {
                $result['require-dev'][$devPackageName] = $result['require'][$devPackageName];
                unset($result['require'][$devPackageName]);
            }
        }

        return $result;
    }

    private function resolveByComposerLock(string $lockPath):array
    {
        $data = $this->loadJson($lockPath);
        if (empty($data)) {
            return [];
        }
        $packages = $data['packages'] ?? [];
        $packagesDev = $data['packages-dev'] ?? [];

        $deps = array_reduce($packages, [$this, 'collectRequirements'], []);
        $depsDev = array_reduce($packagesDev, [$this, 'collectRequirements'], []);
        $result['require'] = $this->buildMap($packages, $deps);
        $result['require-dev'] = $this->buildMap($packagesDev, $depsDev);
        return $result;
    }

    private function saveResult()
    {
        file_put_contents($this->outputPath,
            json_encode($this->result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->debug('Founded dependecies stored at ' . $this->outputPath);
    }

    private function loadJson(string $filePath):array
    {
        $file = file_get_contents($filePath);
        $data = $file ? json_decode($file, true) : [];
        unset($file);
        return $data;
    }

    private function debug(string $message)
    {
        echo $message . PHP_EOL;
    }

    private function collectRequirements(array $acc, array $pkg):array
    {
        return array_merge($acc, isset($pkg['require']) ? array_keys($pkg['require']) : []);
    }

    private function buildMap(array $packages, array $deps): array
    {
        $result = [];
        foreach ($packages as $pkg) {
            if (!in_array($pkg['name'], $deps, true)) {
                $version = $pkg['version'];
                if($version === 'dev-master'){
                    $version.='#'.substr($pkg['source']['reference'], 0, 8);
                }
                $result[$pkg['name']] = $version;
            }
        }
        return $result;
    }
}
