<?php

namespace Ronasit\Larabuilder;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use Ronasit\Larabuilder\Visitors\SetPropertyValue;

class PHPFileBuilder
{
    protected array $ast;
    protected NodeTraverser $traverser;

    public function __construct(
        protected  string $filePath,
    ) {
        $parser = (new ParserFactory())->createForHostVersion();;

        $this->ast = $parser->parse(file_get_contents($this->filePath));
        $this->traverser = new NodeTraverser();
    }

    public function setProperty(string $name, mixed $value): static
    {
        $this->traverser->addVisitor(new SetPropertyValue($name, $value));

        return $this;
    }

    public function save(): void
    {
        $stmts = $this->traverser->traverse($this->ast);

        $fileContent = (new PrettyPrinter\Standard)->prettyPrintFile($stmts);

        file_put_contents($this->filePath, $fileContent);
    }
}
