<?php

namespace Gidato\Filesystem\Nodes;

class Base extends Dir
{
    public function __construct()
    {
        $this->setName('/');
        $this->setMode(0755);
        $this->setWorkingDirectory($this);
    }

    public function getPath() : string
    {
        return '/';
    }

    public function get(string $filename) : ?Node
    {
        if (empty($filename)) {
            return null;
        }

        if ('/' != substr($filename, 0, 1) && $this !== $this->getWorkingDirectory()) {
            return $this->getWorkingDirectory()->get($filename);
        }

        if ('/' != substr($filename, 0, 1)) {
            return null;
        }

        if ($filename == '/') {
            return $this;
        }

        $filename = substr($filename, 1);
        $nodes = explode('/', $filename);
        $firstNode = array_shift($nodes);
        $pathFromNode = implode('/', $nodes);

        if ('.' == $firstNode) {
            return $this->get($pathFromNode);
        }

        if ('..' == $firstNode) {
            return null;
        }

        if (!$this->hasNode($firstNode)) {
            return null;
        }

        return $this->getNode($firstNode)->get($pathFromNode);
    }

    public function setWorkingDirectory(Node $workingDirectory) : void
    {
        $this->workingDirectory = $workingDirectory;
    }

    public function getWorkingDirectory() : Node
    {
        return $this->workingDirectory;
    }
}
