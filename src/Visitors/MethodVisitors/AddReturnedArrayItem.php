<?php

namespace RonasIT\Larabuilder\Visitors\MethodVisitors;

use Illuminate\Support\Arr;
use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Return_;
use RonasIT\Larabuilder\Exceptions\MultipleReturnStatementsException;
use RonasIT\Larabuilder\Exceptions\UnexpectedReturnTypeException;
use RonasIT\Larabuilder\Nodes\PreformattedExpression;
use RonasIT\Larabuilder\Printer;

class AddReturnedArrayItem extends AbstractUpdateMethodVisitor
{
    protected PreformattedExpression $value;
    protected ?PreformattedExpression $key;
    protected Printer $printer;

    public function __construct(
        string $methodName,
        string $value,
        ?string $key = null,
    ) {
        parent::__construct($methodName);

        $this->value = new PreformattedExpression($value);
        $this->key = (!is_null($key)) ? new PreformattedExpression($key) : null;
        $this->printer = new Printer();
    }

    public function updateNode(Node $node): void
    {
        $returnNode = $this->findReturnNode($node->stmts ?? []);

        if (!$returnNode?->expr instanceof Array_) {
            throw new UnexpectedReturnTypeException($this->methodName, 'array', $this->getReturnedValueCode($returnNode));
        }

        if (is_null($this->key)) {
            $returnNode->expr->items[] = new ArrayItem($this->value);

            return;
        }

        foreach ($returnNode->expr->items as $item) {
            if ($item instanceof ArrayItem && $this->isSameKey($item->key)) {
                $item->value = $this->value;

                return;
            }
        }

        $returnNode->expr->items[] = new ArrayItem($this->value, $this->key);
    }

    protected function isSameKey(?Expr $itemKey): bool
    {
        if (is_null($itemKey)) {
            return false;
        }

        return ($itemKey instanceof Scalar && $this->key->parsedExpr instanceof Scalar)
            ? $itemKey->value === $this->key->parsedExpr->value
            : $this->printer->prettyPrintExpr($itemKey) === $this->key->value;
    }

    protected function getReturnedValueCode(?Return_ $returnNode): ?string
    {
        return (!is_null($returnNode?->expr)) ? $this->printer->prettyPrintExpr($returnNode->expr) : null;
    }

    protected function findReturnNode(array $nodes, ?Return_ $foundReturn = null): ?Return_
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

            $foundReturn = $this->findReturnNode($this->getChildNodes($node), $foundReturn);
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
