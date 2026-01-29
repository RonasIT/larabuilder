<?php

namespace RonasIT\Larabuilder\Visitors;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeFinder;
use PhpParser\PrettyPrinter\Standard;
use RonasIT\Larabuilder\Enums\InsertPositionEnum;
use RonasIT\Larabuilder\Exceptions\NodeNotExistException;
use RonasIT\Larabuilder\Nodes\PreformattedCode;
use RonasIT\Larabuilder\Traits\ParserTrait;

class InsertCodeToMethod extends InsertOrUpdateNodeAbstractVisitor
{
    use ParserTrait;

    protected array $preformattedCode = [];

    public function __construct(
        protected string $methodName,
        protected string $code,
        protected InsertPositionEnum $insertPosition,
    ) {
        if (!empty($this->code)) {
            $this->code = Str::chopStart($this->code, '<?php');

            $this->preformattedCode = [new PreformattedCode($this->code)];
        }
    }

    public function beforeTraverse(array $nodes): void
    {
        $node = new NodeFinder()->findFirst($nodes, fn (Node $node) => $this->shouldUpdateNode($node));

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
        return $node instanceof Class_ || $node instanceof Trait_ || $node instanceof Enum_;
    }

    protected function updateNode(Node $node): void
    {
        if (empty($this->code)) {
            return;
        }

        $this->isCodeDuplicated($node->stmts);

        $existingStmts = $node->stmts ?? [];

        $separator = (!empty($existingStmts)) ? [new Nop()] : [];

        $node->stmts = ($this->insertPosition === InsertPositionEnum::Start)
            ? [...$this->preformattedCode, ...$separator, ...$existingStmts]
            : [...$existingStmts, ...$separator, ...$this->preformattedCode];
    }

    protected function isCodeDuplicated(array $methodStmts): void
    {
        $printer = new Standard();

        $parsedCode = $this->parsePHPCode($this->code);

        $haystack = Arr::map($methodStmts, function (Stmt $stmt) use ($printer) {
            $stmt->setAttribute('comments', []);

            return $printer->prettyPrint([$stmt]);
        });

        $needle = Arr::map($parsedCode, fn (Stmt $stmt) => $printer->prettyPrint([$stmt]));

        if ($this->isSubsequence($haystack, $needle)) {
            throw new Exception('Provided code already exists in method body.');
        }
    }

    private function isSubsequence(array $haystack, array $needle): bool
    {
        $needleCount = count($needle);
        $haystackCount = count($haystack);

        for ($i = 0; $i <= $haystackCount - $needleCount; $i++) {
            if (array_slice($haystack, $i, $needleCount) === $needle) {
                return true;
            }
        }

        return false;
    }

    protected function getInsertableNode(): Node
    {
        return new Nop();
    }
}
