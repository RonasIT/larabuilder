<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;
use RonasIT\Larabuilder\Exceptions\InvalidTargetTypeException;

abstract class BaseNodeVisitorAbstract extends NodeVisitorAbstract
{
    abstract protected string $methodName {
        get;
    }

    abstract protected array $parentNodeTypes {
        get;
    }

    protected bool $hasParentNode = false;

    protected const TYPE_ORDER = [
        Namespace_::class,
        Use_::class,
        Class_::class,
        Trait_::class,
        Enum_::class,
        TraitUse::class,
        ClassConst::class,
        Property::class,
        ClassMethod::class,
    ];

    public function afterTraverse(array $nodes): ?array
    {
        if (!$this->hasParentNode) {
            throw new InvalidTargetTypeException($this->methodName, $this->getReadableParentNodeTypes());
        }

        return null;
    }

    protected function getReadableParentNodeTypes(): array
    {
        return array_map(
            fn (string $class) => trim(class_basename($class), '_'),
            $this->parentNodeTypes,
        );
    }

    protected function isParentNode(Node $node): bool
    {
        foreach ($this->parentNodeTypes as $type) {
            if ($node instanceof $type) {
                return true;
            }
        }

        return false;
    }

    protected function getInsertIndex(array $statements, string $insertType): int
    {
        $insertIndex = 0;
        $insertTypeOrder = array_search($insertType, self::TYPE_ORDER);

        foreach ($statements as $index => $statement) {
            foreach (self::TYPE_ORDER as $currentTypeIndex => $type) {
                if ($statement instanceof $type && $currentTypeIndex <= $insertTypeOrder) {
                    $insertIndex = $index + 1;
                }
            }
        }

        return $insertIndex;
    }

    protected function shouldAddEmptyLine(array $stmts, int $index, string $type): bool
    {
        return (isset($stmts[$index]))
            && !($stmts[$index] instanceof Nop)
            && !($stmts[$index] instanceof $type);
    }

    protected function addEmptyLine(array &$nodes, int $index): void
    {
        array_splice($nodes, $index, 0, [new Nop()]);
    }

    protected function prepareNewNode(mixed $parent, mixed $child): mixed
    {
        $this->setParentForNode($child, $parent);

        return $parent;
    }

    protected function setParentForNode(Node $child, Node $parent): void
    {
        $child->setAttribute('parent', $parent);

        if ($child instanceof Array_) {
            foreach ($child->items as $item) {
                $item->setAttribute('parent', $child);

                if ($item->value instanceof Array_) {
                    $this->setParentForNode($item->value, $item);
                }
            }
        }
    }
}
