<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\ParserFactory;
use RonasIT\Larabuilder\Enums\InsertPositionEnum;
use PhpParser\Node\Stmt\ClassMethod;
use Exception;
use PhpParser\Node\Stmt\Nop;
use RonasIT\Larabuilder\Exceptions\NodeNotExistException;

class InsertToMethod extends InsertOrUpdateNodeAbstractVisitor
{
    public function __construct(
        protected string $functionName,
        protected string $code,
        protected InsertPositionEnum $insertPosition,
    ) {
    }

    public function insertNode(Node $node): Node
    {
        throw new NodeNotExistException('Method', $this->functionName);
    }

    protected function shouldUpdateNode(Node $node): bool
    {
        return $node instanceof ClassMethod
            && $this->functionName === $node->name->name;
    }

    protected function isParentNode(Node $node): bool
    {
        return $node instanceof Class_ || $node instanceof Trait_;
    }

    protected function updateNode(Node $node): void
    {
        $newStmt = $this->parsePHPCode($this->code);
        $stmts = $node->stmts ?? [];
        $emptyLine = $node->stmts ? [new Nop()] : [];

        $node->stmts = $this->insertPosition === InsertPositionEnum::Start
            ? [...$newStmt, ...$emptyLine, ...$stmts]
            : [...$stmts, ...$emptyLine, ...$newStmt];
    }

    protected function getInsertableNode(): Node
    {
        return new Node\Stmt\Nop();
    }

    protected function parsePHPCode(string $code): array
    {
        if (!str_starts_with(trim($code), '<?php')) {
            $code = "<?php\n" . $code;
        }

        $parser = (new ParserFactory())->createForHostVersion();

        try {
            $ast = $parser->parse($code);
        } catch (Error $e) {
            throw new Exception('Cannot parse PHP code');
        }

        return $ast;
    }
}
