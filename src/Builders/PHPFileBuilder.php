<?php

namespace RonasIT\Larabuilder\Builders;

use PhpParser\Error;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\ParserFactory;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;
use RonasIT\Larabuilder\Enums\InsertPositionEnum;
use RonasIT\Larabuilder\Exceptions\InvalidPHPFileException;
use RonasIT\Larabuilder\NodeTraverser;
use RonasIT\Larabuilder\Printer;
use RonasIT\Larabuilder\Visitors\AddImports;
use RonasIT\Larabuilder\Visitors\AddTraits;
use RonasIT\Larabuilder\Visitors\InsertCodeToMethod;
use RonasIT\Larabuilder\Visitors\PropertyVisitors\AddArrayPropertyItem;
use RonasIT\Larabuilder\Visitors\PropertyVisitors\RemoveArrayPropertyItem;
use RonasIT\Larabuilder\Visitors\PropertyVisitors\SetPropertyValue;

class PHPFileBuilder
{
    protected array $syntaxTree;
    protected array $oldTokens;
    protected NodeTraverser $traverser;

    public function __construct(
        protected string $filePath,
    ) {
        $parser = new ParserFactory()->createForHostVersion();

        $code = file_get_contents($this->filePath);

        try {
            $this->syntaxTree = $parser->parse($code);
        } catch (Error) {
            throw new InvalidPHPFileException($this->filePath);
        }

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

    public function removeArrayPropertyItem(string $propertyName, array $values): self
    {
        $this->traverser->addVisitor(new RemoveArrayPropertyItem($propertyName, $values));

        return $this;
    }

    public function addImports(array $imports): self
    {
        $this->traverser->addVisitor(new AddImports($imports));

        return $this;
    }

    public function addTraits(array $traits): self
    {
        $this->traverser->addVisitor(new AddTraits($traits));

        $this->addImports($traits);

        return $this;
    }

    public function insertCodeToMethod(string $methodName, string $code, InsertPositionEnum $position = InsertPositionEnum::End): self
    {
        $this->traverser->addVisitor(new InsertCodeToMethod($methodName, $code, $position));

        return $this;
    }

    public function save(): void
    {
        $this->traverser->addVisitor(new CloningVisitor());

        $oldSyntaxTree = $this->syntaxTree;
        $newSyntaxTree = $this->traverser->traverse($this->syntaxTree);

        $fileContent = new Printer()->printFormatPreserving($newSyntaxTree, $oldSyntaxTree, $this->oldTokens);

        file_put_contents($this->filePath, $fileContent);
    }
}
