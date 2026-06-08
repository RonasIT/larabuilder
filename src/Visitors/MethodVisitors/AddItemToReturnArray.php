<?php

namespace RonasIT\Larabuilder\Visitors\MethodVisitors;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use RonasIT\Larabuilder\Contracts\UpdateNodeContract;
use RonasIT\Larabuilder\Enums\DefaultValue;
use RonasIT\Larabuilder\Exceptions\UnexpectedReturnTypeException;
use RonasIT\Larabuilder\Nodes\PreformattedExpression;
use RonasIT\Larabuilder\Printer;

class AddItemToReturnArray extends BaseMethodVisitor implements UpdateNodeContract
{
    protected PreformattedExpression $valueExpr;
    protected ?PreformattedExpression $keyExpr;

    public function __construct(
        protected string $methodName,
        string $value,
        string|DefaultValue $key = DefaultValue::None,
    ) {
        parent::__construct($methodName);

        $this->valueExpr = new PreformattedExpression($value);
        $this->keyExpr = ($key === DefaultValue::None) ? null : new PreformattedExpression($key);
    }

    public function shouldUpdateNode(Node $node): bool
    {
        $isTarget = $node instanceof ClassMethod && $this->methodName === $node->name->name;

        if ($isTarget) {
            $this->hasTargetMethod = true;
        }

        return $isTarget;
    }

    public function updateNode(Node $node): void
    {
        $returnNode = array_find(array_reverse($node->stmts ?? []), fn ($stmt) => $stmt instanceof Return_);

        if (!$returnNode?->expr instanceof Array_) {
            throw new UnexpectedReturnTypeException($this->methodName, 'array', $node->returnType?->toString());
        }

        if (empty($this->keyExpr)) {
            $returnNode->expr->items[] = new ArrayItem($this->valueExpr);

            return;
        }

        $printer = new Printer();

        foreach ($returnNode->expr->items as $item) {
            if ($item instanceof ArrayItem
                && !empty($item->key)
                && $printer->prettyPrintExpr($item->key) === $this->keyExpr->value
            ) {
                $item->value = $this->valueExpr;

                return;
            }
        }

        $returnNode->expr->items[] = new ArrayItem($this->valueExpr, $this->keyExpr);
    }
}
