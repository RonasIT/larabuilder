<?php

namespace Ronasit\Larabuilder;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Ronasit\Larabuilder\Visitors\SetPropertyValue;

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

    public function setProperty(string $name, mixed $value): self
    {
        $this->traverser->addVisitor(new SetPropertyValue($name, $value));

        return $this;
    }

    public function save(): void
    {
        $stmts = $this->traverser->traverse($this->syntaxTree);

        $fileContent = (new Printer())->prettyPrintFile($stmts);

        file_put_contents($this->filePath, $fileContent);
    }
}
