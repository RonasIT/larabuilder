<?php

namespace RonasIT\Larabuilder\Visitors\AppBootstrapVisitors;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use RonasIT\Larabuilder\Support\ValueNodeFactory;
use RonasIT\Larabuilder\ValueOptions\ScheduleOption;

class AddScheduleCommand extends AbstractAppBootstrapVisitor
{
    protected Expression $scheduleStatement;
    protected ValueNodeFactory $valueNodeFactory;

    protected array $options = [];

    public function __construct(
        protected string $command,
        ScheduleOption ...$options,
    ) {
        $this->valueNodeFactory = new ValueNodeFactory();

        $this->options = $options;

        $this->scheduleStatement = $this->buildScheduleCall();

        parent::__construct(
            parentMethod: 'withSchedule',
            targetMethod: 'command',
        );
    }

    protected function buildScheduleCall(): Expression
    {
        $call = new StaticCall(
            class: new Name('Schedule'),
            name: new Identifier('command'),
            args: [
                $this->makeArgument($this->command),
            ],
        );

        foreach ($this->options as $option) {
            $arguments = array_map(fn ($argument) => $this->makeArgument($argument), $option->arguments);

            $call = new MethodCall(
                var: $call,
                name: new Identifier($option->method),
                args: $arguments,
            );
        }

        return new Expression($call);
    }

    protected function makeArgument(mixed $value): Arg
    {
        list($value) = $this->valueNodeFactory->makeNode($value);

        return new Arg($value);
    }

    protected function getInsertableNode(): Expression
    {
        return $this->scheduleStatement;
    }
}
