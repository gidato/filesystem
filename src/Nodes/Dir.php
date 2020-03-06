<?php

namespace Gidato\Filesystem\Nodes;

class Dir extends Node
{
    private $children = [];

    public function __construct(Dir $parent, string $name, int $mode)
    {
        $this->setParent($parent);
        $this->setName($name);
        $this->setMode($mode);
    }

    public function getPath() : string
    {
        return rtrim($this->getParent()->getPath(), '/') . '/' . $this->getName();
    }

    public function isDir() : bool
    {
        return true;
    }

    public function get(string $filename) : ?Node
    {
        if (empty($filename)) {
            return $this;
        }

        $nodes = explode('/', $filename);
        $firstNode = array_shift($nodes);
        $pathFromNode = implode('/', $nodes);

        if ('.' == $firstNode) {
            return $this->get($pathFromNode);
        }

        if ('..' == $firstNode) {
            return $this->getParent()->get($pathFromNode);
        }

        if (!$this->hasNode($firstNode)) {
            return null;
        }

        $nextLevelNode = $this->getNode($firstNode);
        if (empty($pathFromNode)) {
            return $nextLevelNode;
        }

        return $nextLevelNode->get($pathFromNode);
    }

    public function getChildren() : array
    {
        return $this->children;
    }

    public function setNode(Node $node) : void
    {
        $this->children[$node->getName()] = $node;
    }

    public function getNode(string $name) : ?Node
    {
        return $this->children[$name] ?? null;
    }

    public function hasNode(string $name) : bool
    {
        return !empty($this->children[$name]);
    }

    public function unsetNode(Node $node) : void
    {
        $nodeName = $node->getName();
        if ($this->hasNode($nodeName)) {
            unset($this->children[$node->getName()]);
        }
    }

    public function allNodeNames() : array
    {
        return array_keys($this->children);
    }

}
