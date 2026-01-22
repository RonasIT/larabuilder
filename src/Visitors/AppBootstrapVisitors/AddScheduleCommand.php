<?php

namespace RonasIT\Larabuilder\Visitors\AppBootstrapVisitors;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use RonasIT\Larabuilder\DTO\ScheduleFrequencyOptionsDTO;

class AddScheduleCommand extends AbstractAppBootstrapVisitor
{
    protected Expression $renderStatement;
    protected static array $statements = [];
    protected array $frequencyOptions;

    public function __construct(
        protected string $command,
        protected ?string $environment,
        ScheduleFrequencyOptionsDTO ...$frequencyOptions,
    ) {
        $this->frequencyOptions = $frequencyOptions;

        $this->renderStatement = $this->buildRenderCall();

        self::$statements[] = clone $this->renderStatement;

        parent::__construct(
            parentMethod: 'withSchedule',
            targetMethod: 'command',
        );
    }

    protected function insertNode(MethodCall $node): MethodCall
    {
        self::$statements = [];

        $currentStatements = $node->args[0]->value->stmts;

        if (count($currentStatements) === 1 && $currentStatements[0] instanceof Nop) {
            $node->args[0]->value->stmts = [$this->renderStatement];

            return $node;
        }

        $lastExistingStatement = end($currentStatements);

        $this->renderStatement->setAttribute('previous', $lastExistingStatement);

        $node->args[0]->value->stmts[] = $this->renderStatement;

        return $node;
    }

    protected function buildRenderCall(): Expression
    {
        $call = new StaticCall(
            class: new Name('Schedule'),
            name: new Identifier('command'),
            args: [
                new Arg(new String_($this->command)),
            ],
        );

        if ($this->environment) {
            $call = new MethodCall(
                var: $call,
                name: new Identifier('environments'),
                args: [
                    new Arg(new String_($this->environment)),
                ],
            );
        }

        if ($this->frequencyOptions) {
            foreach ($this->frequencyOptions as $option) {
                $args = array_map(fn ($arg) => $this->makeArg($arg), $option->attributes);

                $call = new MethodCall(
                    var: $call,
                    name: new Identifier($option->method->value),
                    args: $args,
                );
            }
        }

        return new Expression($call);
    }

    public function leaveNode(Node $node): Node
    {
        if ($this->shouldInsertParentNode($node)) {
            $node = $this->insertParentNode($node);
        }

        return parent::leaveNode($node);
    }

    protected function shouldInsertParentNode(Node $node): bool
    {
        return ($node instanceof MethodCall)
            && ($node->name->toString() === 'create')
            && $this->isParentMethodMissing($node);
    }

    protected function isParentMethodMissing(Node $node): bool
    {
        $nodeVar = $node->var;

        while ($nodeVar instanceof MethodCall)
        {
            if ($nodeVar->name->toString() === $this->parentMethod) {
                return false;
            }

            $nodeVar = $nodeVar->var;
        }

        return true;
    }

    protected function insertParentNode(Node $node): Node
    {
        $statements = [];

        foreach (self::$statements as $statement) {
            $statements[] = $statement;
            $statements[] = new Nop();
        }

        array_pop($statements);

        $closure = new Closure([
            'returnType' => new Identifier('void'),
            'stmts' => $statements,
        ]);

        $withScheduleCall = new MethodCall($node->var, new Identifier($this->parentMethod), [new Arg($closure)]);

        return new MethodCall($withScheduleCall, new Identifier('create'));
    }
}
