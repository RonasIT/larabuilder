<?php

namespace RonasIT\Larabuilder\Traits;

use Illuminate\Support\Arr;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
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
use PhpParser\PrettyPrinter\Standard;
use RonasIT\Larabuilder\Enums\StatementAttributeEnum;

trait VisitorHelperTrait
{
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

    protected function isSubsequence(array $haystackStatements, array $needleStatements): bool
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

    protected function makeArgument(mixed $value): Arg
    {
        list($value) = $this->getPropertyValue($value);

        return new Arg($value);
    }

    protected function getPropertyValue(mixed $value): array
    {
        $type = get_debug_type($value);

        $value = match ($type) {
            'int' => new Int_($value),
            'array' => $this->makeArrayValue($value),
            'string' => new String_($value),
            'float' => new Float_($value),
            'bool' => $this->makeBoolValue($value),
            'null' => new ConstFetch(new Name('null')),
        };

        return [$value, $type];
    }

    protected function makeBoolValue(bool $value): ConstFetch
    {
        $name = new Name(($value) ? 'true' : 'false');

        return new ConstFetch($name);
    }

    protected function makeArrayValue(array $values): Array_
    {
        $items = [];

        foreach ($values as $key => $val) {
            list($val) = $this->getPropertyValue($val);
            list($key) = $this->getPropertyValue($key);

            $items[] = new ArrayItem($val, $key);
        }

        return new Array_($items);
    }
}
