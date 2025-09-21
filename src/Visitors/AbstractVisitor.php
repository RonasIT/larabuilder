<?php

namespace Ronasit\Larabuilder\Visitors;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

abstract class AbstractVisitor extends NodeVisitorAbstract
{
    abstract protected function isModifyNode(Node $node): bool;

    abstract protected function nodeModificationProcess(Node $node): void;
}