<?php

namespace Gidato\Filesystem\Nodes;

class Mode
{
    private $mode;

    public function __construct(int $mode)
    {
        $this->mode = $mode & 0777;
    }

    public function isReadable() : bool
    {
        return $this->mode & 0400;
    }

    public function isWritable() : bool
    {
        return $this->mode & 0200;
    }

    public function isExecutable() : bool
    {
        return $this->mode & 0100;
    }

    public function toInt() : int
    {
        return $this->mode;
    }
}
