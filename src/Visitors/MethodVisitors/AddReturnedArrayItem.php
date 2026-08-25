<?php

namespace RonasIT\Larabuilder\Visitors\MethodVisitors;

use Illuminate\Support\Arr;
use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Return_;
use RonasIT\Larabuilder\Exceptions\MultipleReturnStatementsException;
use RonasIT\Larabuilder\Exceptions\UnexpectedReturnTypeException;
use RonasIT\Larabuilder\Nodes\PreformattedExpression;
use RonasIT\Larabuilder\Printer;

class AddReturnedArrayItem extends AbstractUpdateMethodVisitor
{
    protected PreformattedExpression $valueExpr;
    protected ?PreformattedExpression $keyExpr;

    public function __construct(
        string $methodName,
        string $value,
        ?string $key = null,
    ) {
        parent::__construct($methodName);

        $this->valueExpr = new PreformattedExpression($value);
        $this->keyExpr = (!is_null($key)) ? new PreformattedExpression($key) : null;
    }

    public function updateNode(Node $node): void
    {
        $returnNodes = $this->findReturnsInScope($node->stmts ?? []);

        if (count($returnNodes) > 1) {
            throw new MultipleReturnStatementsException($this->methodName);
        }

        $returnNode = $returnNodes[0] ?? null;

        if (!$returnNode?->expr instanceof Array_) {
            throw new UnexpectedReturnTypeException($this->methodName, 'array', $node->returnType?->toString());
        }

        if (is_null($this->keyExpr)) {
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

    protected function findReturnsInScope(array $nodes): array
    {
        $returns = [];

        foreach ($nodes as $node) {
            if ($node instanceof Return_) {
                $returns[] = $node;

                continue;
            }

            if ($node instanceof FunctionLike) {
                continue;
            }

            foreach ($node->getSubNodeNames() as $name) {
                foreach (Arr::wrap($node->$name) as $child) {
                    if ($child instanceof Node) {
                        $returns = [...$returns, ...$this->findReturnsInScope([$child])];
                    }
                }
            }
        }

        return $returns;
    }
}
