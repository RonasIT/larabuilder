<?php

namespace RonasIT\Larabuilder;

use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\ParserFactory;
use RonasIT\Larabuilder\NodeTraverser;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;
use RonasIT\Larabuilder\Visitors\SetPropertyValue;
use RonasIT\Larabuilder\Visitors\SetArrayPropertyItems;

class PHPFileBuilder
{
    protected array $syntaxTree;
    protected NodeTraverser $traverser;

    public function __construct(
        protected string $filePath,
    ) {
        $parser = (new ParserFactory())->createForHostVersion();

        $code = file_get_contents($this->filePath);

        $this->syntaxTree = $parser->parse($code);
        $this->traverser = new NodeTraverser();
    }

    public function setProperty(string $name, mixed $value, ?AccessModifierEnum $accessModifier = null): self
    {
        $this->traverser->addVisitor(new SetPropertyValue($name, $value, $accessModifier));

        return $this;
    }

    public function addArrayPropertyItem(string $name, mixed $value): self
    {
        $this->traverser->addVisitor(new SetArrayPropertyItems($name, $value));

        return $this;
    }

    public function save(): void
    {
        $this->traverser->addVisitor(new ParentConnectingVisitor());
        $this->traverser->reverseVisitors();

        $syntaxTree = $this->traverser->traverse($this->syntaxTree);

        $fileContent = (new Printer())->prettyPrintFile($syntaxTree);

        file_put_contents($this->filePath, $fileContent);
    }
}
