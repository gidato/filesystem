<?php

namespace Gidato\Filesystem\Nodes;

class File extends Node
{
    private $contents;

    public function __construct(Dir $parent, string $name, string $contents, int $mode)
    {
        $this->setParent($parent);
        $this->setName($name);
        $this->setContents($contents);
        $this->setMode($mode);
    }

    private function setContents(string $contents) : void
    {
        $this->contents = $contents;
    }

    public function getPath() : string
    {
        return rtrim($this->getParent()->getPath(), '/') . '/' . $this->getName();
    }

    public function isFile() : bool
    {
        return true;
    }

    public function get(string $filename) : ?Node
    {
        return null;
    }

    public function getContents() : string
    {
        return $this->contents;
    }
}
