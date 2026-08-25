<?php

namespace RonasIT\Larabuilder\Visitors\MethodVisitors;

use Illuminate\Support\Arr;
use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
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
        $returnNode = $this->findReturnInScope($node->stmts ?? []);

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

    protected function findReturnInScope(array $nodes, ?Return_ $foundReturn = null): ?Return_
    {
        foreach ($nodes as $node) {
            if ($node instanceof Return_) {
                if (!is_null($foundReturn)) {
                    throw new MultipleReturnStatementsException($this->methodName);
                }

                $foundReturn = $node;

                continue;
            }

            if ($node instanceof FunctionLike || $node instanceof ClassLike) {
                continue;
            }

            $foundReturn = $this->findReturnInScope($this->getChildNodes($node), $foundReturn);
        }

        return $foundReturn;
    }

    protected function getChildNodes(Node $node): array
    {
        $childNodes = [];

        foreach ($node->getSubNodeNames() as $subNodeName) {
            foreach (Arr::wrap($node->$subNodeName) as $subNode) {
                if ($subNode instanceof Node) {
                    $childNodes[] = $subNode;
                }
            }
        }

        return $childNodes;
    }
}
