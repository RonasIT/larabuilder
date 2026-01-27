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
    protected Expression $scheduleStatement;
    protected array $frequencyOptions;

    public function __construct(
        protected string $command,
        protected ?string $environment,
        ScheduleFrequencyOptionsDTO ...$frequencyOptions,
    ) {
        $this->frequencyOptions = $frequencyOptions;

        $this->scheduleStatement = $this->buildScheduleCall();

        parent::__construct(
            parentMethod: 'withSchedule',
            targetMethod: 'command',
        );
    }

    protected function insertNode(MethodCall $node): MethodCall
    {
        $currentStatements = $node->args[0]->value->stmts;

        if (count($currentStatements) === 1 && $currentStatements[0] instanceof Nop) {
            $node->args[0]->value->stmts = [$this->scheduleStatement];

            return $node;
        }

        $lastExistingStatement = end($currentStatements);

        $this->scheduleStatement->setAttribute('previous', $lastExistingStatement);

        $node->args[0]->value->stmts[] = $this->scheduleStatement;

        return $node;
    }

    protected function buildScheduleCall(): Expression
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
        if ($node instanceof MethodCall) {
            if ($this->shouldInsertParentNode($node)) {
                $node = $this->insertParentNode($node);
            }

            if ($node->var->getAttribute('wasCreated')) {
                $node->var = parent::leaveNode($node->var);
            }
        }

        return parent::leaveNode($node);
    }

    protected function shouldInsertParentNode(Node $node): bool
    {
        return ($node->name->toString() === 'create')
            && $this->isParentMethodMissing($node);
    }

    protected function isParentMethodMissing(Node $node): bool
    {
        $nodeVar = $node->var;

        while ($nodeVar instanceof MethodCall) {
            if ($nodeVar->name->toString() === $this->parentMethod) {
                return false;
            }

            $nodeVar = $nodeVar->var;
        }

        return true;
    }

    protected function insertParentNode(Node $node): Node
    {
        $closure = new Closure([
            'returnType' => new Identifier('void'),
        ]);

        $scheduleCall = new MethodCall($node->var, new Identifier($this->parentMethod), [new Arg($closure)]);
        $scheduleCall->setAttribute('wasCreated', true);
        $scheduleCall
            ->args[0]
            ->value
            ->setAttribute('parent', $scheduleCall);

        $createCall = new MethodCall($scheduleCall, new Identifier('create'));

        return $createCall;
    }
}
