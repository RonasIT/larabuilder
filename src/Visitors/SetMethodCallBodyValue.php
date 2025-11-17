<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

class SetMethodCallBodyValue extends NodeVisitorAbstract
{
    protected ?array $importNodes;

    public function __construct(
        protected string $method,
        string $value,
    ) {
        $parser = (new ParserFactory())->createForHostVersion();
        $this->importNodes = $parser->parse('<?php' . $value);
    }

    public function leaveNode(Node $node): Node
    {
        if ($node instanceof MethodCall &&
            $node->name->name === $this->method &&
            $this->shouldInsertNode($node)) {
            $this->insertNode($node);
        }

        return $node;
    }

    protected function shouldInsertNode(MethodCall $node): bool
    {
        foreach ($node->args[0]->value->stmts as $stmt) {
            if ($this->nodesAreEqualIgnoringAttributes($stmt, $this->importNodes[0])) {
                return false;
            }
        }

        return true;
    }

    protected function insertNode(MethodCall $node): MethodCall
    {
        $currentStatements = $node->args[0]->value->stmts;

        if (count($currentStatements) === 1 && $currentStatements[0] instanceof Nop) {
            $node->args[0]->value->stmts = $this->importNodes;

            return $node;
        }

        $lastExistingStatement = end($currentStatements);
        $this->importNodes[0]->setAttribute('previous', $lastExistingStatement);

        $node->args[0]->value->stmts = array_merge($currentStatements, $this->importNodes);

        return $node;
    }

    protected function nodesAreEqualIgnoringAttributes(mixed $a, mixed $b): bool
    {
        if (is_array($a) && is_array($b)) {
            if (count($a) !== count($b)) {
                return false;
            }

            foreach ($a as $index => $item) {
                if (!$this->nodesAreEqualIgnoringAttributes($item, $b[$index])) {
                    return false;
                }
            }

            return true;
        }

        if (!$a instanceof Node || !$b instanceof Node) {
            return $a === $b;
        }

        if (get_class($a) !== get_class($b)) {
            return false;
        }

        foreach ($a->getSubNodeNames() as $subNodeName) {
            if (!$this->nodesAreEqualIgnoringAttributes($a->$subNodeName, $b->$subNodeName)) {
                return false;
            }
        }

        return true;
    }
}
