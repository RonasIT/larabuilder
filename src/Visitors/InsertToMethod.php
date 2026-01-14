<?php

namespace RonasIT\Larabuilder\Visitors;

use Exception;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use RonasIT\Larabuilder\Enums\InsertPositionEnum;
use RonasIT\Larabuilder\Exceptions\NodeNotExistException;

class InsertToMethod extends InsertOrUpdateNodeAbstractVisitor
{
    public function __construct(
        protected string $methodName,
        protected string $code,
        protected InsertPositionEnum $insertPosition,
    ) {
    }

    public function insertNode(Node $node): Node
    {
        throw new NodeNotExistException('Method', $this->methodName);
    }

    public function beforeTraverse(array $nodes): void
    {
        $nodeFinder = new NodeFinder();

        $node = $nodeFinder->findFirst($nodes, fn (Node $node) => $this->shouldUpdateNode($node));

        if (is_null($node)) {
            throw new NodeNotExistException('Method', $this->methodName);
        }
    }

    protected function shouldUpdateNode(Node $node): bool
    {
        return $node instanceof ClassMethod
            && $this->methodName === $node->name->name;
    }

    protected function isParentNode(Node $node): bool
    {
        return $node instanceof Class_ || $node instanceof Trait_;
    }

    protected function updateNode(Node $node): void
    {
        $newStmt = $this->parsePHPCode($this->code);
        $existingStmts = $node->stmts ?? [];
        $separator = (!empty($existingStmts)) ? [new Nop()] : [];

        $node->stmts = ($this->insertPosition === InsertPositionEnum::Start)
            ? [...$newStmt, ...$separator, ...$existingStmts]
            : [...$existingStmts, ...$separator, ...$newStmt];
    }

    protected function getInsertableNode(): Node
    {
        return new Nop();
    }

    protected function parsePHPCode(string $code): array
    {
        if (!str_starts_with(trim($code), '<?php')) {
            $code = "<?php\n{$code}";
        }

        $parser = (new ParserFactory())->createForHostVersion();

        try {
            $ast = $parser->parse($code);
        } catch (Error $error) {
            throw new Exception("Cannot parse PHP code: {$error->getMessage()}");
        }

        return $ast;
    }
}
