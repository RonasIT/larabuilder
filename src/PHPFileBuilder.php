<?php

namespace Ronasit\Larabuilder;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Ronasit\Larabuilder\Visitors\SetPropertyValue;

class PHPFileBuilder
{
    protected array $ast;
    protected NodeTraverser $traverser;

    public function __construct(
        protected  string $filePath,
    ) {
        $parser = (new ParserFactory())->createForHostVersion();

        $code = file_get_contents($this->filePath);

        $this->ast = $parser->parse($code);
        $this->traverser = new NodeTraverser();
    }

    public function setProperty(string $name, mixed $value, ?int $accessModifier = null): static
    {
        $this->traverser->addVisitor(new SetPropertyValue($name, $value, $accessModifier));

        return $this;
    }

    public function save(): void
    {
        $stmts = $this->traverser->traverse($this->ast);

        $fileContent = (new Printer())->prettyPrintFile($stmts);

        file_put_contents($this->filePath, $fileContent);
    }
}
