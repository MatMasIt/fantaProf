<?php
use \Bramus\Ansi\Ansi;
use \Bramus\Ansi\Writers\StreamWriter;
use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class VoidTest extends Test
{
    public function __construct()
    {
        parent::__construct("A void test", "Does nothing", true);
    }
    public function run(array $resultValues): bool
    {
        return true;
    }
}
