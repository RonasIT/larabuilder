<?php

namespace RonasIT\Larabuilder;

use PhpParser\ParserFactory;
use RonasIT\Larabuilder\NodeTraverser;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use RonasIT\Larabuilder\Visitors\SetPropertyValue;
use RonasIT\Larabuilder\Visitors\AddArrayPropertyItem;

class PHPFileBuilder
{
    protected array $syntaxTree;
    protected array $oldTokens;
    protected NodeTraverser $traverser;

    public function __construct(
        protected string $filePath,
    ) {
        $parser = (new ParserFactory())->createForHostVersion();

        $code = file_get_contents($this->filePath);

        $this->syntaxTree = $parser->parse($code);
        $this->oldTokens = $parser->getTokens();
        $this->traverser = new NodeTraverser();
    }

    public function setProperty(string $name, mixed $value, AccessModifierEnum $accessModifier = AccessModifierEnum::Public): self
    {
        $this->traverser->addVisitor(new SetPropertyValue($name, $value, $accessModifier));

        return $this;
    }

    public function addArrayPropertyItem(string $propertyName, mixed $value): self
    {
        $this->traverser->addVisitor(new AddArrayPropertyItem($propertyName, $value));

        return $this;
    }

    public function save(): void
    {
        $this->traverser->addVisitor(new ParentConnectingVisitor());
        $this->traverser->addVisitor(new CloningVisitor());

        $oldSyntaxTree = $this->syntaxTree;
        $newSyntaxTree = $this->traverser->traverse($this->syntaxTree);

        $fileContent = (new Printer())->printFormatPreserving($newSyntaxTree, $oldSyntaxTree, $this->oldTokens);

        file_put_contents($this->filePath, $fileContent);
    }
}
