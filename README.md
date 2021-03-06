# Gidato / Filesystem

This package replicates many of the raw filesystem function calls within a class.
Using the Disk option passes the call directly through to the builtin PHP handlers.

This allows the Memory class to be used during testing to avoid having to create real files.

## Installation
```

composer require gidato/filesystem

```

## Example Use

```
<?php

use Gidato\Filesystem\Filesystem;
use Gidato\Filesystem\Memory as InMemoryFilesystem;

function test(Filesystem $filesystem)
{
    // example use, always use Filesystem which is the interface
    // eg
    $filesystem->mkdir('/hello/world');
}

$filesystem = new InMemoryFilesystem();

test($filesystem);

```

The following functions are replicated (taken directly from the interface):
```

public function chdir(string $directory) : bool;
public function chmod(string $filename , int $mode) : bool;
public function copy(string $source , string $dest) : bool;
public function file_exists(string $filename) : bool;
public function file_get_contents(string $filename); // string or false
public function file_put_contents(string $filename, $data, int $flags = 0 ); // int or false
public function file(string $filename, int $flags = 0 ); // array or false
public function fileperms(string $filename) : ?int;
public function getcwd() : string;
public function glob(string $pattern, int $flags = 0); // array or false
public function is_dir(string $filename) : bool;
public function is_file(string $filename) : bool;
public function is_link(string $filename) : bool;
public function is_readable(string $filename) : bool;
public function is_writable(string $filename) : bool;
public function mkdir(string $pathname, int $mode = 0777, bool $recursive = FALSE) : bool;
public function readlink(string $path); // string or false
public function rmdir(string $directory) : bool;
public function scandir(string $directory, int $sorting_order = SCANDIR_SORT_ASCENDING); // array or false
public function symlink(string $target, string $link) : bool;
public function touch(string $filename) : bool;
public function umask(?int $mask) : int;
public function unlink(string $filename) : bool;

```


## License

This software is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
