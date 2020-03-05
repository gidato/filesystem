<?php

namespace Gidato\Filesystem\Nodes;

use Gidato\Filesystem\Filesystem;

class Link extends Node
{
    private $target;
    private $filesystem;

    public function __construct(Dir $parent, string $name, Filesystem $filesystem, string $target, int $mode)
    {
        $this->setParent($parent);
        $this->setName($name);
        $this->setMode($mode);
        $this->setTarget($target);
        $this->filesystem = $filesystem;
    }

    private function setTarget(string $target) : void
    {
        $this->target = $target;
    }

    public function getTarget() : string
    {
        return $this->target;
    }

    public function getPath() : string
    {
        return rtrim($this->getParent()->getPath(), '/') . '/' . $this->getName();
    }

    public function isDir() : bool
    {
        return $this->filesystem->is_dir($this->target);
    }

    public function isFile() : bool
    {
        return $this->filesystem->is_file($this->target);
    }

    public function isLink() : bool
    {
        return true;
    }

    public function get(string $filename) : ?Node
    {
        $target = $this->filesystem->get($this->target);
        if (null === $target) {
            return null;
        }
        return $target->get($filename);
    }

    public function getContents() : string
    {
        if ($this->isDir()) {
            throw RuntimeException('Should never be called');
        }

        $target = $this->filesystem->get($this->target);
        if (null === $target) {
            return '';
        }

        return $target->getContents();
    }

}
