<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\BuilderHelpers;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Trait_;
use RonasIT\Larabuilder\Contracts\InsertNodeContract;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;
use RonasIT\Larabuilder\Enums\DefaultValue;
use RonasIT\Larabuilder\Exceptions\NodeAlreadyExistsException;
use RonasIT\Larabuilder\Nodes\PreformattedCode;
use RonasIT\Larabuilder\ValueOptions\MethodParam;
use RonasIT\Larabuilder\ValueOptions\MethodParams;

class AddMethod extends BaseNodeVisitorAbstract implements InsertNodeContract
{
    protected array $allowedParentNodesTypes = [
        Class_::class,
        Trait_::class,
        Enum_::class,
    ];

    protected PreformattedCode $code;

    public function __construct(
        protected string $name,
        string $code,
        protected MethodParams $params,
        protected ?string $returnType = null,
        protected ?AccessModifierEnum $accessModifier = null,
        protected bool $static = false,
        protected bool $returnsByRef = false,
    ) {
        $this->code = new PreformattedCode($code);
    }

    protected function modify(Node $node): Node
    {
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof ClassMethod && $stmt->name->name === $this->name) {
                throw new NodeAlreadyExistsException('Method', $this->name);
            }
        }

        return parent::modify($node);
    }

    public function getInsertableNode(): Node
    {
        $flags = ($this->accessModifier ?? AccessModifierEnum::Public)->value;

        if ($this->static) {
            $flags |= Modifiers::STATIC;
        }

        return new ClassMethod($this->name, [
            'flags' => $flags,
            'byRef' => $this->returnsByRef,
            'params' => $this->buildParams(),
            'returnType' => $this->returnType !== null ? BuilderHelpers::normalizeType($this->returnType) : null,
            'stmts' => [$this->code],
        ]);
    }

    protected function buildParams(): array
    {
        return array_map(fn (MethodParam $param) => new Param(
            var: new Variable($param->name),
            default: $param->default !== DefaultValue::None ? BuilderHelpers::normalizeValue($param->default) : null,
            type: $param->type !== null ? BuilderHelpers::normalizeType($param->type) : null,
            byRef: $param->byRef,
            variadic: $param->variadic,
        ), $this->params->params);
    }
}
