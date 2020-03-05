<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error\Warning;

abstract class FilesystemTest extends TestCase
{
    private $filesystem;
    protected $base = '';
    protected $testClass;
    protected $canTestWritable = false;

    public function setUp() : void
    {
        $this->filesystem = New $this->testClass();
    }

    public function testChdir()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->chdir($this->base ?: '/'));
        $this->assertEquals($this->base ?: '/', (string) $this->filesystem->getcwd());
        $this->assertTrue($this->filesystem->chdir($this->base . '/demo/dir'));
        $this->assertEquals($this->base . '/demo/dir', (string) $this->filesystem->getcwd());

        // no such directory
        try {
            $this->assertFalse($this->filesystem->chdir($this->base . '/demo/dir2'));
            $e = null;
        } catch (\Exception | \Error $e) {
        } finally {
            $this->assertNotNull($e);
            $this->assertEquals(Warning::class, get_class($e));
            $this->assertEquals("chdir(): No such file or directory (errno 2)", $e->getMessage());
        }

    }

    public function testChmod()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo/dir/file') !== false);
        $this->assertTrue($this->filesystem->chmod($this->base . '/demo/dir/file', 0700));
        $this->assertEquals(0700, 0777 & $this->filesystem->fileperms($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->chmod($this->base . '/demo/dir/file', 0765));
        $this->assertEquals(0765, 0777 & $this->filesystem->fileperms($this->base . '/demo/dir/file'));
    }

    public function testCopyFileToFile()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir1', 0755, true));
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir2', 0755, true));
        $this->assertTrue($this->filesystem->file_put_contents($this->base . '/demo/dir1/file1','hello') !== false);
        $this->assertTrue($this->filesystem->copy($this->base . '/demo/dir1/file1',$this->base . '/demo/dir2/file2'));
        $this->assertEquals('hello', $this->filesystem->file_get_contents($this->base . '/demo/dir1/file1'));
        $this->assertEquals('hello', $this->filesystem->file_get_contents($this->base . '/demo/dir2/file2'));
    }
    public function testFileExists()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->file_put_contents($this->base . '/demo/dir/file','hello') !== false);
        $this->assertTrue($this->filesystem->file_exists($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->file_exists($this->base . '/demo/dir'));
        $this->assertFalse($this->filesystem->file_exists($this->base . '/demo/dir/file2'));
        $this->assertFalse($this->filesystem->file_exists($this->base . '/demo/dir2'));
    }


    public function testFileGetContents()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->file_put_contents($this->base . '/demo/dir/file','hello world') !== false);
        $this->assertEquals('hello world', $this->filesystem->file_get_contents($this->base . '/demo/dir/file'));
    }


    public function testFilePutContents()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->file_put_contents($this->base . '/demo/dir/file','hello world') !== false);
        $this->assertEquals('hello world', $this->filesystem->file_get_contents($this->base . '/demo/dir/file'));
    }


    public function testFile()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->file_put_contents(
            $this->base . '/demo/dir/file',
            "\n\nLine 3\nLine 4\n\nLine 6"
        ) !== false);

        $this->assertEquals(["\n","\n","Line 3\n", "Line 4\n", "\n", "Line 6"], $this->filesystem->file($this->base . '/demo/dir/file'));
        $this->assertEquals(["\n","\n","Line 3\n", "Line 4\n", "\n", "Line 6"], $this->filesystem->file($this->base . '/demo/dir/file', FILE_SKIP_EMPTY_LINES));
        $this->assertEquals(["", "", "Line 3", "Line 4", "", "Line 6"], $this->filesystem->file($this->base . '/demo/dir/file', FILE_IGNORE_NEW_LINES));
        $this->assertEquals(["Line 3", "Line 4", "Line 6"], $this->filesystem->file($this->base . '/demo/dir/file', FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES));
    }

    public function testGetcwd()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->chdir($this->base ?: '/'));
        $this->assertEquals($this->base ?: '/', (string) $this->filesystem->getcwd());
        $this->assertTrue($this->filesystem->chdir($this->base . '/demo/dir'));
        $this->assertEquals($this->base . '/demo/dir', (string) $this->filesystem->getcwd());
    }


    public function testIsDir()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir/file', $this->base . '/demo/dir/file2'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir', $this->base . '/demo/dir2'));
        $this->assertTrue($this->filesystem->is_dir($this->base . '/demo/dir'));
        $this->assertFalse($this->filesystem->is_dir($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->is_dir($this->base . '/demo/dir2')); // link to dir
        $this->assertFalse($this->filesystem->is_dir($this->base . '/demo/dir/file2')); // link to file
        $this->assertFalse($this->filesystem->is_dir($this->base . '/demo/dir3')); // not set up
    }


    public function testIsFile()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir/file', $this->base . '/demo/dir/file2'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir', $this->base . '/demo/dir2'));
        $this->assertFalse($this->filesystem->is_file($this->base . '/demo/dir'));
        $this->assertTrue($this->filesystem->is_file($this->base . '/demo/dir/file'));
        $this->assertFalse($this->filesystem->is_file($this->base . '/demo/dir2')); // link to dir
        $this->assertTrue($this->filesystem->is_file($this->base . '/demo/dir/file2')); // link to file
        $this->assertFalse($this->filesystem->is_file($this->base . '/demo/dir3')); // not set up
    }


    public function testIsLink()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir/file', $this->base . '/demo/dir/file2'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir', $this->base . '/demo/dir2'));

        $this->assertFalse($this->filesystem->is_link($this->base . '/demo/dir'));
        $this->assertFalse($this->filesystem->is_link($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->is_link($this->base . '/demo/dir/file2'));
        $this->assertTrue($this->filesystem->is_link($this->base . '/demo/dir2'));
    }

    public function testIsReadable()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir/file', $this->base . '/demo/dir/file2'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir', $this->base . '/demo/dir2'));

        $this->assertTrue($this->filesystem->is_readable($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->is_readable($this->base . '/demo/dir'));
        $this->assertTrue($this->filesystem->is_readable($this->base . '/demo/dir/file2'));
        $this->assertTrue($this->filesystem->is_readable($this->base . '/demo/dir2'));

        $this->assertTrue($this->filesystem->chmod($this->base . '/demo/dir/file', 0300));
        $this->assertTrue($this->filesystem->chmod($this->base . '/demo/dir', 0300));
        $this->assertTrue($this->filesystem->chmod($this->base . '/demo/dir/file2', 0300));
        $this->assertTrue($this->filesystem->chmod($this->base . '/demo/dir2', 0300));

        $this->assertFalse($this->filesystem->is_readable($this->base . '/demo/dir/file'));
        $this->assertFalse($this->filesystem->is_readable($this->base . '/demo/dir'));
        $this->assertFalse($this->filesystem->is_readable($this->base . '/demo/dir/file2'));
        $this->assertFalse($this->filesystem->is_readable($this->base . '/demo/dir2'));

        $this->assertFalse($this->filesystem->is_readable($this->base . '/demo/another'));

        // tidy up as auto delete won't be able to read the directory to see what to delete
        $this->assertTrue($this->filesystem->unlink($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->unlink($this->base . '/demo/dir/file2'));
        $this->assertTrue($this->filesystem->unlink($this->base . '/demo/dir2'));
        $this->assertTrue($this->filesystem->rmdir($this->base . '/demo/dir'));
    }



    public function testIsWritable()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir/file', $this->base . '/demo/dir/file2'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir', $this->base . '/demo/dir2'));

        $this->assertTrue($this->filesystem->is_writable($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->is_writable($this->base . '/demo/dir'));
        $this->assertTrue($this->filesystem->is_writable($this->base . '/demo/dir/file2'));
        $this->assertTrue($this->filesystem->is_writable($this->base . '/demo/dir2'));

        if ($this->canTestWritable) {
            $this->assertTrue($this->filesystem->chmod($this->base . '/demo/dir/file', 0500));
            $this->assertTrue($this->filesystem->chmod($this->base . '/demo/dir', 0500));
            $this->assertTrue($this->filesystem->chmod($this->base . '/demo/dir/file2', 0500));
            $this->assertTrue($this->filesystem->chmod($this->base . '/demo/dir2', 0500));

            $this->assertFalse($this->filesystem->is_writable($this->base . '/demo/dir/file'));
            $this->assertFalse($this->filesystem->is_writable($this->base . '/demo/dir'));
            $this->assertFalse($this->filesystem->is_writable($this->base . '/demo/dir/file2'));
            $this->assertFalse($this->filesystem->is_writable($this->base . '/demo/dir2'));
        }
    }

    public function testMkDir()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->is_dir($this->base . '/demo/dir'));

        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir/dir2'));
        $this->assertTrue($this->filesystem->is_dir($this->base . '/demo/dir/dir2'));
        $this->assertEquals(0777, $this->filesystem->fileperms($this->base . '/demo/dir/dir2') & 0777);

        try {
            $this->assertFalse($this->filesystem->mkdir($this->base . '/demo/dir2/dir2'));
            $e = null;
        } catch (\Exception | \Error $e) {
        } finally {
            $this->assertNotNull($e);
            $this->assertEquals(Warning::class, get_class($e));
            $this->assertEquals("mkdir(): No such file or directory", $e->getMessage());
        }

        try {
            $this->assertFalse($this->filesystem->mkdir($this->base . '/demo/dir'));
            $e = null;
        } catch (\Exception | \Error $e) {
        } finally {
            $this->assertNotNull($e);
            $this->assertEquals(Warning::class, get_class($e));
            $this->assertEquals("mkdir(): File exists", $e->getMessage());
        }

        $this->assertEquals(0, $this->filesystem->umask(022));
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir/dir3'));
        $this->assertTrue($this->filesystem->is_dir($this->base . '/demo/dir/dir3'));
        $this->assertEquals(0755, $this->filesystem->fileperms($this->base . '/demo/dir/dir3') & 0777);

        $this->assertTrue($this->filesystem->touch($this->base . '/demo/dir/file'));
        try {
            $this->assertFalse($this->filesystem->mkdir($this->base . '/demo/dir/file/dir'));
            $e = null;
        } catch (\Exception | \Error $e) {
        } finally {
            $this->assertNotNull($e);
            $this->assertEquals(Warning::class, get_class($e));
            $this->assertEquals("mkdir(): Not a directory", $e->getMessage());
        }
    }

    public function testReadLink()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir/file', $this->base . '/demo/dir/file2'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir', $this->base . '/demo/dir2'));

        $this->assertEquals($this->base . '/demo/dir/file', $this->filesystem->readlink($this->base . '/demo/dir/file2'));
        $this->assertEquals($this->base . '/demo/dir', $this->filesystem->readlink($this->base . '/demo/dir2'));

        try {
            $this->assertFalse($this->filesystem->readlink($this->base . '/demo/dir/file3'));
            $e = null;
        } catch (\Exception | \Error $e) {
        } finally {
            $this->assertNotNull($e);
            $this->assertEquals(Warning::class, get_class($e));
            $this->assertEquals("readlink(): No such file or directory", $e->getMessage());
        }

        try {
            $this->assertFalse($this->filesystem->readlink($this->base . '/demo/dir/file'));
            $e = null;
        } catch (\Exception | \Error $e) {
        } finally {
            $this->assertNotNull($e);
            $this->assertEquals(Warning::class, get_class($e));
            $this->assertEquals("readlink(): Invalid argument", $e->getMessage());
        }

    }


    /**
     * for some reason, rmdir seems to throw warnings!
     */
    public function testRmDir()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir1/dir2/dir3', 0777, true));
        $this->assertTrue($this->filesystem->is_dir($this->base . '/demo/dir1/dir2/dir3'));
        $this->assertTrue($this->filesystem->rmdir($this->base . '/demo/dir1/dir2/dir3'));
        $this->assertFalse($this->filesystem->is_dir($this->base . '/demo/dir1/dir2/dir3'));

        try {
            $this->filesystem->rmdir($this->base . '/demo/dir1');
            $e = null;
        } catch (\Exception | \Error $e) {
        } finally {
            $this->assertNotNull($e);
            $this->assertEquals(Warning::class, get_class($e));
            $this->assertEquals("rmdir({$this->base}/demo/dir1): Directory not empty", $e->getMessage());
        }

        try {
            $this->filesystem->rmdir($this->base . '/demo/dir2');
            $e = null;
        } catch (\Exception | \Error $e) {
        } finally {
            $this->assertNotNull($e);
            $this->assertEquals(Warning::class, get_class($e));
            $this->assertEquals("rmdir({$this->base}/demo/dir2): No such file or directory", $e->getMessage());
        }

    }

    public function testScanDir()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir1/dir2', 0777, true));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo/dir1/file2'));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo/dir1/file1'));
        $files = $this->filesystem->scandir($this->base . '/demo/dir1', SCANDIR_SORT_NONE);
        sort($files); // could be in any order, so hard to really test, other than sorting them
        $this->assertEquals(['.', '..', 'dir2', 'file1', 'file2'], $files);
        $this->assertEquals(['.', '..', 'dir2', 'file1', 'file2'], $this->filesystem->scandir($this->base . '/demo/dir1', SCANDIR_SORT_ASCENDING));
        $this->assertEquals(['.', '..', 'dir2', 'file1', 'file2'], $this->filesystem->scandir($this->base . '/demo/dir1'));
        $this->assertEquals(['file2', 'file1', 'dir2', '..', '.'], $this->filesystem->scandir($this->base . '/demo/dir1', SCANDIR_SORT_DESCENDING));
    }


    public function testSymlink()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir/file', $this->base . '/demo/dir/file2'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir', $this->base . '/demo/dir2'));
        $this->assertTrue($this->filesystem->is_link($this->base . '/demo/dir2'));
        $this->assertTrue($this->filesystem->is_link($this->base . '/demo/dir/file2'));
        $this->assertEquals($this->base . '/demo/dir', $this->filesystem->readlink($this->base . '/demo/dir2'));
        $this->assertEquals($this->base . '/demo/dir/file', $this->filesystem->readlink($this->base . '/demo/dir/file2'));

        // target doesn't exist;
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/dir/file3', $this->base . '/demo/dir/file4'));
        $this->assertEquals($this->base . '/demo/dir/file3', $this->filesystem->readlink($this->base . '/demo/dir/file4'));

        // link path already exists -- warning;
        try {
            $this->assertFalse($this->filesystem->symlink($this->base . '/demo/dir/file', $this->base . '/demo/dir/file'));
            $e = null;
        } catch (\Exception | \Error $e) {
        } finally {
            $this->assertNotNull($e);
            $this->assertEquals(Warning::class, get_class($e));
            $this->assertEquals("symlink(): File exists", $e->getMessage());
        }
    }


    public function testTouch()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->is_file($this->base . '/demo/dir/file'));
        $this->assertEquals('', $this->filesystem->file_get_contents($this->base . '/demo/dir/file'));

        $this->assertEquals(9, $this->filesystem->file_put_contents($this->base . '/demo/dir/file','some text'));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo/dir/file'));
        $this->assertEquals('some text', $this->filesystem->file_get_contents($this->base . '/demo/dir/file'));
    }

    public function testUmask()
    {
        $this->assertEquals(0,$this->filesystem->umask(0));
        $this->assertEquals(0,$this->filesystem->umask());
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo'));
        $this->assertEquals(0777, 0777 & $this->filesystem->fileperms($this->base . '/demo'));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo/file'));
        $this->assertEquals(0666, 0777 & $this->filesystem->fileperms($this->base . '/demo/file'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo/file', $this->base .'/demo/file2'));
        $this->assertEquals(0666, 0777 & $this->filesystem->fileperms($this->base . '/demo/file2'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo', $this->base .'/demolink'));
        $this->assertEquals(0777, 0777 & $this->filesystem->fileperms($this->base . '/demolink'));

        $this->assertEquals(0,$this->filesystem->umask(022));
        $this->assertEquals(022,$this->filesystem->umask());
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo2'));
        $this->assertEquals(0755, 0777 & $this->filesystem->fileperms($this->base . '/demo2'));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo2/file'));
        $this->assertEquals(0644, 0777 & $this->filesystem->fileperms($this->base . '/demo2/file'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo2/file', $this->base .'/demo2/file2'));
        $this->assertEquals(0644, 0777 & $this->filesystem->fileperms($this->base . '/demo2/file2'));
        $this->assertTrue($this->filesystem->symlink($this->base . '/demo2', $this->base .'/demo2link'));
        $this->assertEquals(0755, 0777 & $this->filesystem->fileperms($this->base . '/demo2link'));

        $this->assertEquals(022,$this->filesystem->umask(0));
    }

    public function testUnlink()
    {
        $this->assertTrue($this->filesystem->mkdir($this->base . '/demo/dir', 0755, true));
        $this->assertTrue($this->filesystem->touch($this->base . '/demo/dir/file'));
        $this->filesystem->symlink($this->base . '/demo/dir/file', $this->base . '/demo/dir/file2');
        $this->assertTrue($this->filesystem->is_file($this->base . '/demo/dir/file'));
        $this->assertTrue($this->filesystem->is_file($this->base . '/demo/dir/file2'));
        $this->assertTrue($this->filesystem->unlink($this->base . '/demo/dir/file2'));
        $this->assertTrue($this->filesystem->unlink($this->base . '/demo/dir/file'));
        $this->assertFalse($this->filesystem->is_file($this->base . '/demo/dir/file'));
        $this->assertFalse($this->filesystem->is_file($this->base . '/demo/dir/file2'));

        // no file at path
        try {
            $this->assertFalse($this->filesystem->unlink($this->base . '/demo/dir/file3'));
            $e = null;
        } catch (\Exception | \Error $e) {
        } finally {
            $this->assertNotNull($e);
            $this->assertEquals(Warning::class, get_class($e));
            $this->assertEquals("unlink({$this->base}/demo/dir/file3): No such file or directory", $e->getMessage());
        }

        // directory at path
        try {
            $this->assertFalse($this->filesystem->unlink($this->base . '/demo/dir'));
            $e = null;
        } catch (\Exception | \Error $e) {
        } finally {
            $this->assertNotNull($e);
            $this->assertEquals("unlink({$this->base}/demo/dir): Operation not permitted", $e->getMessage());
        }
    }

}
