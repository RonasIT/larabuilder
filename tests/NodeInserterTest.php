<?php

namespace RonasIT\Larabuilder\Tests;

use PhpParser\Modifiers;
use PhpParser\Node\Const_;
use PhpParser\Node\Name;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\ParserFactory;
use RonasIT\Larabuilder\NodeTraverser;
use RonasIT\Larabuilder\Printer;
use RonasIT\Larabuilder\Support\NodeInserter;

class NodeInserterTest extends TestCase
{
    protected NodeInserter $inserter;
    protected array $oldSyntaxTree;
    protected array $newSyntaxTree;
    protected array $oldTokens;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inserter = new NodeInserter();
    }

    public function testInsertMixedNodes(): void
    {
        $this->prepareFixture('class.php');

        $classNode = (new NodeFinder())->findFirstInstanceOf($this->newSyntaxTree, Class_::class);

        $this->inserter->insertNodes($classNode->stmts, [
            new Property(Modifiers::PUBLIC, [new PropertyItem('anotherProperty')]),
            new ClassMethod('newMethod', ['flags' => Modifiers::PUBLIC]),
            new ClassConst([new Const_('NEW_CONST', new Int_(42))], Modifiers::PUBLIC),
            new ClassMethod('anotherMethod', ['flags' => Modifiers::PUBLIC]),
            new Property(Modifiers::PUBLIC, [new PropertyItem('newProperty')]),
            new TraitUse([new Name('NewTrait')]),
            new ClassConst([new Const_('ANOTHER_CONST', new Int_(0))], Modifiers::PUBLIC),
            new TraitUse([new Name('AnotherTrait')]),
        ]);

        $this->assertSame(
            $this->getFixture('class_with_mixed_nodes_inserted.php'),
            $this->printResult(),
        );
    }

    protected function prepareFixture(string $fixture): void
    {
        $parser = (new ParserFactory())->createForHostVersion();

        $code = file_get_contents($this->generateOriginalStructurePath($fixture));

        $syntaxTree = $parser->parse($code);

        $this->oldTokens = $parser->getTokens();

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new CloningVisitor());

        $this->oldSyntaxTree = $syntaxTree;
        $this->newSyntaxTree = $traverser->traverse($syntaxTree);
    }

    protected function printResult(): string
    {
        return (new Printer())->printFormatPreserving($this->newSyntaxTree, $this->oldSyntaxTree, $this->oldTokens);
    }
}
