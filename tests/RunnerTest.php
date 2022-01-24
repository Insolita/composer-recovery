<?php

use Insolita\ComposerRecovery\Runner;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase
{
    public function setUp():void
    {
        parent::setUp();
        foreach (['app1.json', 'app2.json', 'app3.json'] as $file) {
            if (file_exists(__DIR__ . '/_output/' . $file)) {
                unlink(__DIR__ . '/_output/' . $file);
            }
        }
    }

    public function testRecoverByComposerLock()
    {
        $projectPath = __DIR__ . '/stub/app1';
        $outPath = __DIR__ . '/_output/app1.json';
        $expectedPath = __DIR__ . '/stub/app1_expected.json';
        $this->assertFileNotExists($outPath);
        $runner = new Runner($projectPath, $outPath);
        $code = $runner->run();
        $this->assertEquals(0, $code);
        $this->assertFileExists($outPath);
        $this->assertJsonFileEqualsJsonFile($outPath, $expectedPath);
    }

    public function testRecoverByInstalledJson()
    {
        $projectPath = __DIR__ . '/stub/app2';
        $outPath = __DIR__ . '/_output/app2.json';
        $expectedPath = __DIR__ . '/stub/app2_expected.json';
        $this->assertFileNotExists($outPath);
        $runner = new Runner($projectPath, $outPath);
        $code = $runner->run();
        $this->assertEquals(0, $code);
        $this->assertFileExists($outPath);
        $this->assertJsonFileEqualsJsonFile($outPath, $expectedPath);
    }

    public function testRecoverByInstalledJsonWithPackages()
    {
        $projectPath = __DIR__ . '/stub/app3';
        $outPath = __DIR__ . '/_output/app3.json';
        $expectedPath = __DIR__ . '/stub/app3_expected.json';
        $this->assertFileNotExists($outPath);
        $runner = new Runner($projectPath, $outPath);
        $code = $runner->run();
        $this->assertEquals(0, $code);
        $this->assertFileExists($outPath);
        $this->assertJsonFileEqualsJsonFile($outPath, $expectedPath);
    }
}
