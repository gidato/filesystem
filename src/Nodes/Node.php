<?php

namespace Gidato\Filesystem\Nodes;

abstract class Node
{
    private $name;
    private $parent;
    private $mode;

    protected function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    protected function setParent(Node $parent) : void
    {
        $this->parent = $parent;
    }

    public function getParent() : ?Node
    {
        return $this->parent;
    }

    public function hasParent() : bool
    {
        return !empty($this->parent);
    }

    public function setMode(int $mode) : void
    {
        $this->mode = new Mode($mode);
    }

    public function getMode() : Mode
    {
        return $this->mode;
    }

    public function isReadable() : bool
    {
        return $this->mode->isReadable();
    }

    public function isWritable() : bool
    {
        return $this->mode->isWritable();
    }

    public function isDir() : bool
    {
        return false;
    }

    public function isFile() : bool
    {
        return false;
    }

    public function isLink() : bool
    {
        return false;
    }

    abstract public function getPath() : string;
    abstract public function get(string $filename): ?Node;

    public function __toString()
    {
        return $this->getPath();
    }
}
