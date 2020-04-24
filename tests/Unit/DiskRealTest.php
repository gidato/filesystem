<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Gidato\Filesystem\Disk;

class DiskRealTest extends TestCase
{
    private $testDir;
    private $filesystem;

    public function setUp() : void
    {
        $this->testDir = __DIR__ . '/tmp';
        $this->deleteDirectory($this->testDir);
        mkdir($this->testDir);
        $this->filesystem = New Disk();
    }

    public function tearDown() : void
    {
        $this->deleteDirectory($this->testDir);
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

    public function testChdir()
    {
        $testdir = $this->testDir . DIRECTORY_SEPARATOR . 'testdir';
        // create file & set base perms to 0700
        mkdir($testdir);

        $current = getcwd();
        $this->filesystem->chdir($testdir);
        $this->assertEquals($testdir, getcwd());
        $this->assertNotEquals($current, getcwd());
    }

    public function testChmod()
    {
        $filename = $this->testDir . DIRECTORY_SEPARATOR . 'testfile.txt';
        // create file & set base perms to 0700
        touch($filename);
        chmod($filename, 0700);

        $this->assertEquals(0700, fileperms($filename) & 0777);
        $this->filesystem->chmod($filename, 0755);
        $this->assertEquals(0700, fileperms($filename) & 0777);
    }

    public function testCopyFileToFile()
    {
        $source = $this->testDir . DIRECTORY_SEPARATOR . 'srcfile.txt';
        $dest = $this->testDir . DIRECTORY_SEPARATOR . 'destfile.txt';
        // create file
        file_put_contents($source, 'abc');

        $this->assertFalse(file_exists($dest));
        $this->filesystem->copy($source, $dest);
        $this->assertTrue(file_exists($dest));
        $this->assertEquals('abc', file_get_contents($dest));
    }

    public function testFileExists()
    {
        $file1 = $this->testDir . DIRECTORY_SEPARATOR . 'file1.txt';
        $file2 = $this->testDir . DIRECTORY_SEPARATOR . 'file2.txt';
        file_put_contents($file1, 'abc');

        $this->assertTrue($this->filesystem->file_exists($file1));
        $this->assertFalse($this->filesystem->file_exists($file2));
    }

    public function testFileGetContents()
    {
        $file = $this->testDir . DIRECTORY_SEPARATOR . 'file.txt';
        file_put_contents($file, 'abc');
        $this->assertEquals('abc', $this->filesystem->file_get_contents($file));
    }

    public function testFilePutContents()
    {
        $file = $this->testDir . DIRECTORY_SEPARATOR . 'file.txt';
        // create & put
        $this->filesystem->file_put_contents($file, 'abc');
        $this->assertEquals('abc', file_get_contents($file));

        // append
        $this->filesystem->file_put_contents($file, 'def', FILE_APPEND);
        $this->assertEquals('abcdef', file_get_contents($file));

        // overwrite
        $this->filesystem->file_put_contents($file, 'abc');
        $this->assertEquals('abc', file_get_contents($file));
    }

    public function testFile()
    {
        $file = $this->testDir . DIRECTORY_SEPARATOR . 'file.txt';
        file_put_contents($file, implode("\n",['a','b','c']));

        $this->assertEquals(['a','b','c'], $this->filesystem->file($file, FILE_IGNORE_NEW_LINES));
        $this->assertEquals(["a\n","b\n","c"], $this->filesystem->file($file));
    }

    public function testFileperms()
    {
        $filename = $this->testDir . DIRECTORY_SEPARATOR . 'testfile.txt';
        touch($filename);

        chmod($filename, 0755);
        $this->assertEquals(0755, 0777 & $this->filesystem->fileperms($filename));

        chmod($filename, 0700);
        clearstatcache();
        $this->assertEquals(0700, 0777 & $this->filesystem->fileperms($filename));
    }

    public function testFilesize()
    {
        $file = $this->testDir . DIRECTORY_SEPARATOR . 'file.txt';
        file_put_contents($file, 'sample');

        $this->assertEquals(6, $this->filesystem->filesize($file));
    }

    public function testGetcwd()
    {
        chdir($this->testDir);
        $this->assertEquals($this->testDir, $this->filesystem->getcwd());
    }

    public function testGlob()
    {
        file_put_contents($this->testDir . DIRECTORY_SEPARATOR . 'file.txt', 'content');
        file_put_contents($this->testDir . DIRECTORY_SEPARATOR . 'file2.txt', 'content');

        $globbed = $this->filesystem->glob($this->testDir . DIRECTORY_SEPARATOR . '*');
        $this->assertCount(2, $globbed);
        $this->assertEquals($this->testDir . DIRECTORY_SEPARATOR . 'file.txt', $globbed[0]);
        $this->assertEquals($this->testDir . DIRECTORY_SEPARATOR . 'file2.txt', $globbed[1]);

        $globbed = $this->filesystem->glob($this->testDir . DIRECTORY_SEPARATOR . '*2*');
        $this->assertCount(1, $globbed);
        $this->assertEquals($this->testDir . DIRECTORY_SEPARATOR . 'file2.txt', $globbed[0]);
    }


    public function testIsDir()
    {
        $file = $this->testDir . DIRECTORY_SEPARATOR . 'file.txt';
        $dir = $this->testDir . DIRECTORY_SEPARATOR . 'testdir';
        touch($file);
        mkdir($dir);

        $this->assertFalse($this->filesystem->is_dir($file));
        $this->assertTrue($this->filesystem->is_dir($dir));
    }

    public function testIsFile()
    {
        $file = $this->testDir . DIRECTORY_SEPARATOR . 'file.txt';
        $dir = $this->testDir . DIRECTORY_SEPARATOR . 'testdir';
        touch($file);
        mkdir($dir);

        $this->assertFalse($this->filesystem->is_file($dir));
        $this->assertTrue($this->filesystem->is_file($file));
    }

    public function testIsLink()
    {
        $file = $this->testDir . DIRECTORY_SEPARATOR . 'file.txt';
        $dir = $this->testDir . DIRECTORY_SEPARATOR . 'testdir';
        $link = $this->testDir . DIRECTORY_SEPARATOR . 'link';
        touch($file);
        mkdir($dir);
        symlink($file, $link);

        $this->assertFalse($this->filesystem->is_link($dir));
        $this->assertFalse($this->filesystem->is_link($file));
        $this->assertTrue($this->filesystem->is_link($link));
    }

    public function testIsReadable()
    {
        $file1 = $this->testDir . DIRECTORY_SEPARATOR . 'file1.txt';
        $file2 = $this->testDir . DIRECTORY_SEPARATOR . 'file2.txt';
        touch($file1);

        $this->assertTrue($this->filesystem->is_readable($file1));
        $this->assertFalse($this->filesystem->is_readable($file2));
    }

    public function testIsWritable()
    {
        $file1 = $this->testDir . DIRECTORY_SEPARATOR . 'file1.txt';
        $file2 = $this->testDir . DIRECTORY_SEPARATOR . 'file2.txt';
        touch($file1);

        $this->assertTrue($this->filesystem->is_writable($file1));
        $this->assertFalse($this->filesystem->is_writable($file2));
        $this->assertFalse($this->filesystem->is_writable('/'));
    }

    public function testMkDir()
    {
        $dir = $this->testDir . DIRECTORY_SEPARATOR . 'testdir';

        $this->assertTrue($this->filesystem->mkdir($dir));
        $this->assertTrue(is_dir($dir));

        try {
            $recursive = $this->testDir . DIRECTORY_SEPARATOR . 'testdir/fred/bob/john';
            $this->filesystem->mkdir($recursive);
            $e = null;
        } catch (\Exception | \Error $e) {
        } finally {
            $this->assertNotNull($e);
            $this->assertEquals('mkdir(): No such file or directory', $e->getMessage());
        }

        $this->filesystem->mkdir($recursive, 0715, true);
        $this->assertTrue(is_dir($recursive));
        $this->assertEquals(0715, fileperms($recursive) & 0777);
    }

    public function testReadLink()
    {
        $file = $this->testDir . DIRECTORY_SEPARATOR . 'file.txt';
        $link = $this->testDir . DIRECTORY_SEPARATOR . 'link';
        touch($file);
        symlink($file, $link);

        $this->assertEquals($file, $this->filesystem->readlink($link));
    }

    public function testRmDir()
    {
        $dir = $this->testDir . DIRECTORY_SEPARATOR . 'testdir';
        $recursive = $this->testDir . DIRECTORY_SEPARATOR . 'testdir/fred/bob/john';
        mkdir($recursive, 0777, true);

        // check all set up ok
        $this->assertTrue(is_dir($recursive));
        try {
            $this->filesystem->rmdir($dir);
            $e = null;
        } catch (\Exception | \Error $e) {
        } finally {
            $this->assertNotNull($e);
            $this->assertEquals("rmdir({$dir}): Directory not empty", $e->getMessage());
        }

        $this->filesystem->rmdir($recursive);
        $this->assertFalse(is_dir($recursive));
    }

    public function testScanDir()
    {
        touch($this->testDir . DIRECTORY_SEPARATOR . 'file2');
        touch($this->testDir . DIRECTORY_SEPARATOR . 'file1');
        $this->assertEquals(['.', '..', 'file1', 'file2'], $this->filesystem->scandir($this->testDir));
        $this->assertEquals(['file2', 'file1', '..', '.'], $this->filesystem->scandir($this->testDir, SCANDIR_SORT_DESCENDING));
    }

    public function testSymlink()
    {
        $file = $this->testDir . DIRECTORY_SEPARATOR . 'file.txt';
        $link = $this->testDir . DIRECTORY_SEPARATOR . 'link';
        touch($file);
        $this->filesystem->symlink($file, $link);

        $this->assertFalse(is_link($file));
        $this->assertTrue(is_link($link));
        $this->assertEquals($file, readlink($link));
    }

    public function testTouch()
    {
        $file = $this->testDir . DIRECTORY_SEPARATOR . 'file.txt';

        $this->assertFalse(file_exists($file));
        $this->filesystem->touch($file);
        $this->assertTrue(file_exists($file));
    }

    public function testUnlink()
    {
        $file = $this->testDir . DIRECTORY_SEPARATOR . 'file.txt';
        $link = $this->testDir . DIRECTORY_SEPARATOR . 'link';
        touch($file);
        symlink($file, $link);

        $this->assertTrue(file_exists($link));
        $this->assertTrue(file_exists($file));
        $this->filesystem->unlink($link);
        $this->filesystem->unlink($file);
        $this->assertFalse(file_exists($link));
        $this->assertFalse(file_exists($file));
    }
}
