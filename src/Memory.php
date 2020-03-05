<?php

namespace Gidato\Filesystem;

use Gidato\Filesystem\Nodes\Base;
use Gidato\Filesystem\Nodes\Dir;
use Gidato\Filesystem\Nodes\File;
use Gidato\Filesystem\Nodes\Link;
use Gidato\Filesystem\Nodes\Node;
use InvalidArgumentException;

class Memory implements Filesystem
{
    private $structure;
    private $mask = 0;
    private $lastError; // helps with testing;

    public function __construct()
    {
        $this->structure = new Base();
    }

    public function get(string $filename) : ?Node
    {
        return $this->structure->get($filename);
    }

    public function chdir(string $directoryName) : bool
    {
        $directory = $this->get($directoryName);
        if (null === $directory) {
            trigger_error("chdir(): No such file or directory (errno 2)", E_USER_WARNING);
            return $this->error("Directory {$directoryName} not found");
        }

        $this->structure->setWorkingDirectory($directory);
        return $this->noErrors();
    }

    public function chmod(string $filename , int $mode) : bool
    {
        $file = $this->get($filename);
        if (null == $file) {
            return $this->error("Path {$filename} not found");
        }

        if (!$file->isWritable()) {
            return $this->error("Path {$filename} not writable");
        }

        $file->setMode($mode);
        return $this->noErrors();
    }

    public function copy(string $sourcePath , string $destPath) : bool
    {
        $source = $this->get($sourcePath);
        [$destDirPath, $destName] = $this->getDirectoryAndNameFromPath($destPath);
        $destDir = $this->get($destDirPath);
        $dest = $this->get($destPath);

        if (null == $source) {
            return $this->error("Source {$sourcePath} not found");
        }

        if (null == $destDir) {
            return $this->error("Destination directory {$destDirPath} does not exist");
        }

        if (!$source->isFile()) {
            return $this->error("Source  {$sourcePath} is not a file");
        }

        if (!$source->isReadable()) {
            return $this->error("Source  {$sourcePath} is not readable");
        }

        if (null !== $dest && !$dest->isWritable()) {
            return $this->error("Destination  {$destPath} is not writable");
        }

        if (null !== $dest && !$dest->isFile()) {
            return $this->error("Destination  {$destPath} is not a file, but exists");
        }

        if (null === $dest && !$destDir->isWritable()) {
            return $this->error("Destination directory {$destDirPath} is not writable");
        }

        $destDir->setNode(new File($destDir, $destName, $source->getContents(), 0666 ^ $this->mask));
        return $this->noErrors();
    }

    public function file_exists(string $filename) : bool
    {
        return $this->structure->get($filename) !== null;
    }

    public function file_get_contents(string $filename) // string or false
    {
        $file = $this->get($filename);

        if (null === $file) {
            return $this->error("File {$filename} not found");
        }

        if (!$file->isFile()) {
            return $this->error("Path {$filename} is not a file");
        }

        if (!$file->isReadable()) {
            return $this->error("File {$filename} is not readable");
        }

        return $file->getContents();
    }

    public function file_put_contents(string $filename, $data, int $flags = 0 ) // int or false
    {
        $file = $this->get($filename);
        if (null !== $file && !$file->isWritable()) {
            return $this->error("Path exists already and is not writable ({$filename})");
        }

        if (null !== $file && !$file->isFile()) {
            return $this->error("Path exists already and is not a file ({$filename})");
        }

        $dir = $this->get(dirname($filename));
        if (null === $dir) {
            return $this->error("Parent directory does not exist ({$filename})");
        }

        if (null === $file && !$dir->isWritable()) {
            return $this->error("Parent directory is not writable ({$dir})");
        }

        if (FILE_APPEND & $flags && null !== $file && !$file->isReadable()) {
            return $this->error("Request to append, but file is not readable ({$filename})");
        }

        $dataExisting = (FILE_APPEND & $flags && null !== $file) ? $file->getContents() : '';
        $dir->setNode(new File($dir, basename($filename), $dataExisting . $data, 0666 ^ $this->mask));
        return strlen($data);
    }

    public function file(string $filename, int $flags = 0 ) // array or false
    {
        $data = $this->file_get_contents($filename);
        if (false === $data) {
            return false;
        }

        if (empty($data)) {
            return [];
        }

        $lines = explode("\n", $data);


        $emptyLastLine = empty($lines[count($lines) - 1]);

        if (0 == ($flags & FILE_IGNORE_NEW_LINES)) {
            $lines = array_map(function($line) { return $line . "\n"; }, $lines);
        }

        if ($flags & FILE_SKIP_EMPTY_LINES) {
            $lines = array_values(array_filter($lines));
        }

        if (!$emptyLastLine) {
            // remove the carriage return on last line if there wasn't one originally
            $line = array_pop($lines);
            $line = rtrim($line, "\n");
            $lines[] = $line;
        }

        return $lines;
    }

    public function fileperms(string $filename) : ?int
    {
        $file = $this->get($filename);
        if (null === $file) {
            return $this->error("Path {$filename} not found");
        }

        return $file->getMode()->toInt();
    }

    public function getcwd() : string
    {
        return (string) $this->structure->getWorkingDirectory();
    }

    public function glob(string $pattern, int $flags = 0) // array or false
    {
        return glob($pattern, $flags);
    }

    public function is_dir(string $filename) : bool
    {
        $path = $this->get($filename);
        return (empty($path)) ? false : $path->isDir();
    }

    public function is_file(string $filename) : bool
    {
        $path = $this->get($filename);
        return (empty($path)) ? false : $path->isFile();
    }

    public function is_link(string $filename) : bool
    {
        $path = $this->get($filename);
        return (empty($path)) ? false : $path->isLink();
    }

    public function is_readable(string $filename) : bool
    {
        $path = $this->get($filename);
        return (empty($path)) ? false : $path->isReadable();
    }

    public function is_writable(string $filename) : bool
    {
        $path = $this->get($filename);
        return (empty($path)) ? false : $path->isWritable();
    }

    public function mkdir(string $pathname, int $mode = 0777, bool $recursive = FALSE) : bool
    {
        $mode = $mode ^ $this->mask;

        if (empty($pathname)) {
            trigger_error("mkdir(): No such file or directory", E_USER_WARNING);
            return $this->error("Pathname is empty");
        }

        if ($this->file_exists($pathname)) {
            trigger_error("mkdir(): File exists", E_USER_WARNING);
            return $this->error("Pathname ({$pathname}) exists");
        }

        $dir = $this->get(dirname($pathname));
        if (null === $dir && $recursive) {
            if (!$this->mkdir(dirname($pathname), $mode | 0700, $recursive)) {
                return false;
            }
            $dir = $this->get(dirname($pathname));
        }

        if (null === $dir) {
            trigger_error("mkdir(): No such file or directory", E_USER_WARNING);
            return $this->error("Destination directory (" . dirname($pathname) . ") does not exist");
        }

        if (!$dir->isDir()) {
            trigger_error("mkdir(): Not a directory", E_USER_WARNING);
            return $this->error("Path contains a file ({$dir->getPath()})");
        }

        $dir->setNode(new Dir($dir, basename($pathname), $mode));
        return $this->noErrors();
    }

    public function readlink(string $path) // may return string or false
    {

        $node = $this->get($path);
        if (null === $node) {
            trigger_error("readlink(): No such file or directory", E_USER_WARNING);
            return $this->error("Path {$path} does not exist");
        }

        if (!$node->isReadable()) {
            return $this->error("Path {$path} is not readable");
        }

        if (!$node->isLink($path)) {
            trigger_error("readlink(): Invalid argument", E_USER_WARNING);
            return $this->error("Path {$path} is not a link");
        }

        return (string) $node->getTarget();
    }

    public function rmdir(string $path) : bool
    {
        $directory = $this->get($path);

        if (null === $directory) {
            trigger_error("rmdir($path): No such file or directory", E_USER_WARNING);
            return $this->error("Path {$path} does not exist");
        }

        if (!$directory->isWritable()) {
            return $this->error("Directory {$path} is not writable");
        }

        if ([] != $directory->allNodeNames()) {
            trigger_error("rmdir($path): Directory not empty", E_USER_WARNING);
            return $this->error("Directory {$path} is not empty");
        }

        $directory->getParent()->unsetNode($directory);
        return $this->noErrors();
    }

    public function scandir(string $path, int $sorting_order = SCANDIR_SORT_ASCENDING) // array or false
    {
        $directory = $this->get($path);

        if (null === $directory) {
            return $this->error("Path {$path} does not exist");
        }

        if (!$directory->isReadable()) {
            return $this->error("Directory {$path} is not readable");
        }

        $names = $directory->allNodeNames();
        array_unshift($names, '.', '..');

        if ($sorting_order == SCANDIR_SORT_ASCENDING) {
            sort($names);
        }

        if ($sorting_order == SCANDIR_SORT_DESCENDING) {
            rsort($names);
        }

        return $names;
    }

    public function symlink(string $targetPath, string $linkPath) : bool
    {
        $linkDir = $this->get(dirname($linkPath));

        if ($this->file_exists($linkPath)) {
            trigger_error("symlink(): File exists", E_USER_WARNING);
            return $this->error("Link path {$linkPath} already exists");
        }

        if (!$linkDir->isWritable()) {
            return $this->error("Link directory {$linkDir} is not writable");
        }

        if ($this->is_dir($targetPath)) {}

        $linkDir->setNode(new Link(
            $linkDir,
            basename($linkPath),
            $this,
            $targetPath,
            ($this->is_dir($targetPath) ? 0777 : 0666) ^ $this->mask
        ));
        return $this->noErrors();
    }

    public function touch(string $filename) : bool
    {
        if ($this->file_exists($filename)) {
            return $this->noErrors();
        }

        return ($this->file_put_contents($filename, '') !== false);
    }

    public function umask(?int $mask = null) : int
    {
        $current = $this->mask;
        if (null !== $mask) {
            $this->mask = $mask;
        }
        return $current;
    }

    public function unlink(string $filename) : bool
    {
        $file = $this->get($filename);

        if (null === $file) {
            trigger_error("unlink($filename): No such file or directory", E_USER_WARNING);
            return $this->error("File {$filename} does not exist");
        }

        if (!$file->isWritable()) {
            return $this->error("File {$filename} is not writable");
        }

        if ($file->isDir() && !$file->isLink()) {
            trigger_error("unlink($filename): Operation not permitted", E_USER_WARNING);
            return $this->error("Path {$filename} is a directory");
        }

        $file->getParent()->unsetNode($file);
        return $this->noErrors();
    }

    private function error(string $errorMessage) : bool
    {
        $this->lastError = $errorMessage;
        return false;
    }

    private function noErrors() : bool
    {
        unset($this->lastError);
        return true;
    }

    public function getLastError() : ?string
    {
        return $this->lastError ?? null;
    }

    private function getDirectoryAndNameFromPath(string $path) : array
    {
        return [dirname($path), basename($path)];
    }

}
