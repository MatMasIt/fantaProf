<?php

use \Bramus\Ansi\Ansi;
use \Bramus\Ansi\Writers\BufferWriter;
use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

abstract class Test
{
    protected string $title, $description;
    protected Ansi $ansi;
    protected array $resultValues;
    public function __construct(string $title, string $description)

    {
        $this->title = $title;
        $this->description = $description;
        $this->ansi = new Ansi(new BufferWriter('php://stdout'));
        $this->resultValues = [];
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    private function setTitle(string $title): void
    {
        $this->title = $title;
    }
    public function getDescription(): string
    {
        return $this->description;
    }
    private function setDescription(string $description): void
    {
        $this->title = $description;
    }
    public function run(array $resultValues): bool
    {
        return true;
    }
    public function printLog(): void
    {
        echo $this->ansi->flush();
    }

    protected function setResultValues(array $resultValues): void
    {
        $this->resultValues = $resultValues;
    }

    public function getResultValues(): array
    {
        return $this->resultValues;
    }
}
