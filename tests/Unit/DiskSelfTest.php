<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Gidato\Filesystem\Disk;

class DiskSelfTest extends FilesystemTest
{
    protected $testClass = Disk::class;

    public function setUp() : void
    {
        umask(0);
        $this->base = __DIR__ . '/tmp';
        $this->deleteDirectory($this->base);
        mkdir($this->base);
        parent::setUp();
    }

    public function tearDown() : void
    {
        $this->deleteDirectory($this->base);
    }

    private function deleteDirectory(string $dir) : void
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
          (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }

        rmdir($dir);
    }

}
