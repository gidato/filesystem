<?php

namespace Gidato\Filesystem;

class Disk implements Filesystem
{
    public function chdir(string $directory) : bool
    {
        return chdir($directory);
    }

    public function chmod(string $filename , int $mode) : bool
    {
        return chmod($filename, $mode);
    }

    public function copy(string $source , string $dest) : bool
    {
        return copy($source, $dest);
    }

    public function file_exists(string $filename) : bool
    {
        return file_exists($filename);
    }

    public function file_get_contents(string $filename) // string or false
    {
        return file_get_contents($filename);
    }

    public function file_put_contents(string $filename, $data, int $flags = 0 ) // int or false
    {
        return file_put_contents($filename, $data, $flags);
    }

    public function file(string $filename, int $flags = 0 ) // array or false
    {
        return file($filename, $flags);
    }

    public function fileperms(string $filename) : ?int
    {
        clearstatcache();
        return fileperms($filename);
    }

    public function filesize(string $filename) // int or false;
    {
        return filesize($filename);
    }

    public function getcwd() : string
    {
        return getcwd();
    }

    public function glob(string $pattern, int $flags = 0) // array or false
    {
        return glob($pattern, $flags);
    }

    public function is_dir(string $filename) : bool
    {
        return is_dir($filename);
    }

    public function is_file(string $filename) : bool
    {
        return is_file($filename);
    }

    public function is_link(string $filename) : bool
    {
        return is_link($filename);
    }

    public function is_readable(string $filename) : bool
    {
        return is_readable($filename);
    }

    public function is_writable(string $filename) : bool
    {
        return is_writable($filename);
    }

    public function mkdir(string $pathname, int $mode = 0777, bool $recursive = FALSE) : bool
    {
        return mkdir($pathname, $mode, $recursive);
    }

    public function readlink(string $path) // may return string or false
    {
        return readlink($path);
    }

    public function rename(string $oldname, string $newname) : bool
    {
        return rename($oldname, $newname);
    }

    public function rmdir(string $directoryname) : bool
    {
        return rmdir($directoryname);
    }

    public function scandir(string $directory, int $sorting_order = SCANDIR_SORT_ASCENDING) // array or false
    {
        return scandir($directory, $sorting_order);
    }

    public function symlink(string $target, string $link) : bool
    {
        return symlink($target, $link);
    }

    public function touch(string $filename) : bool
    {
        return touch($filename);
    }

    public function umask(?int $mask = null) : int
    {
        if (null === $mask) {
            return umask();
        }

        return umask($mask);
    }

    public function unlink(string $filename) : bool
    {
        return unlink($filename);
    }


}
