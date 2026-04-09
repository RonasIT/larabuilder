<?php

namespace RonasIT\Larabuilder\Visitors;

use Illuminate\Support\Arr;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt;
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
use PhpParser\PrettyPrinter\Standard;
use RonasIT\Larabuilder\Enums\StatementAttributeEnum;
use RonasIT\Larabuilder\Exceptions\InvalidStructureTypeException;

abstract class BaseNodeVisitorAbstract extends NodeVisitorAbstract
{
    protected const array ANY_TYPE = [];

    abstract protected array $allowedParentNodesTypes {
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

    public function leaveNode(Node $node): Node
    {
        if ($this->isParentNode($node)) {
            $this->hasParentNode = true;

            return $this->handleParentNode($node);
        }

        return $node;
    }

    public function afterTraverse(array $nodes): ?array
    {
        if (!empty($this->allowedParentNodesTypes) && !$this->hasParentNode) {
            throw new InvalidStructureTypeException(class_basename(get_called_class()), $this->getReadableAllowedParentNodesTypes());
        }

        return null;
    }

    protected function getReadableAllowedParentNodesTypes(): array
    {
        return array_map(
            fn (string $class) => trim(class_basename($class), '_'),
            $this->allowedParentNodesTypes,
        );
    }

    protected function isParentNode(Node $node): bool
    {
        return array_any($this->allowedParentNodesTypes, fn ($type) => $node instanceof $type);
    }

    protected function handleParentNode(Node $node): Node
    {
        return $node;
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
        $child->setAttribute(StatementAttributeEnum::Parent->value, $parent);

        if ($child instanceof Array_) {
            foreach ($child->items as $item) {
                $item->setAttribute(StatementAttributeEnum::Parent->value, $child);

                if ($item->value instanceof Array_) {
                    $this->setParentForNode($item->value, $item);
                }
            }
        }
    }

    protected function isCodeDuplicated(array $existingStatements, array $statementsToCheck): bool
    {
        if (empty($existingStatements) || empty($statementsToCheck)) {
            return false;
        }

        $haystack = $this->normalizeStatements($existingStatements);
        $needle = $this->normalizeStatements($statementsToCheck);

        return $this->isSubsequence($haystack, $needle);
    }

    protected function normalizeStatements(array $statements): array
    {
        $printer = new Standard();

        return Arr::map($statements, function (Stmt $statement) use ($printer) {
            $stmtCopy = clone $statement;

            $stmtCopy->setAttribute(StatementAttributeEnum::Comments->value, []);

            return $printer->prettyPrint([$stmtCopy]);
        });
    }

    private function isSubsequence(array $haystackStatements, array $needleStatements): bool
    {
        $needleCount = count($needleStatements);
        $haystackCount = count($haystackStatements);

        for ($i = 0; $i <= $haystackCount - $needleCount; $i++) {
            if (array_slice($haystackStatements, $i, $needleCount) === $needleStatements) {
                return true;
            }
        }

        return false;
    }
}
