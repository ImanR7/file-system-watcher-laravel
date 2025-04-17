<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use SplFileInfo;
use Tests\TestCase;
use App\Services\FileWatcher\WatcherManager;

class FileWatcherTest extends TestCase
{
    protected $watcher;
    protected $testPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->watcher = app(WatcherManager::class);
        $this->testPath = storage_path('fswatch/tests');
        File::deleteDirectory($this->testPath);
        File::makeDirectory($this->testPath, 0755, true);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->testPath);
        parent::tearDown();
    }

    #[Test]
    public function it_processes_txt_file_and_appends_bacon_ipsum()
    {
        $path = $this->testPath . '/sample.txt';
        file_put_contents($path, "Hello World");

        $this->watcher->handle(new SplFileInfo($path), 'modified');

        $content = file_get_contents($path);
        $this->assertStringContainsString('GENERATED_BY_TXT_WATCHER', $content);
    }

    #[Test]
    public function it_sends_json_file_to_endpoint()
    {
        Http::fake();

        $path = $this->testPath . '/sample.json';
        file_put_contents($path, json_encode(['test' => true]));

        $this->watcher->handle(new SplFileInfo($path), 'created');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://fswatcher.requestcatcher.com/';
        });
    }

    #[Test]
    public function it_optimizes_jpg_file()
    {
        $path = $this->testPath . '/test.jpg';
        copy(__DIR__.'/../fixtures/test.jpg', $path);

        $beforeSize = filesize($path);
        $this->watcher->handle(new SplFileInfo($path), 'created');
        clearstatcache();
        $afterSize = filesize($path);

        $this->assertLessThan($beforeSize, $afterSize);
    }

    #[Test]
    public function it_extracts_zip_file()
    {
        $zip = new \ZipArchive();
        $zipFilePath = $this->testPath . '/archive.zip';
        $zip->open($zipFilePath, \ZipArchive::CREATE);
        $zip->addFromString('hello.txt', 'Hello ZIP');
        $zip->close();

        $this->watcher->handle(new SplFileInfo($zipFilePath), 'created');

        $this->assertTrue(file_exists($this->testPath . '/archive/hello.txt'));
    }

    #[Test]
    public function it_replaces_deleted_file_with_meme()
    {
        $deletedFile = $this->testPath . '/deleted.txt';
        file_put_contents($deletedFile, 'some text');

        $this->watcher->handle(new SplFileInfo($deletedFile), 'deleted');
        unlink($deletedFile);

        $newFilePath = $this->testPath . '/deleted.jpg';
        $this->assertTrue(file_exists($newFilePath));
    }
}
