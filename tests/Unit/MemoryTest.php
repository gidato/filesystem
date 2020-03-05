<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Gidato\Filesystem\Memory;

class MemorytTest extends FilesystemTest
{
    protected $testClass = Memory::class;
    protected $canTestWritable = true;

}
