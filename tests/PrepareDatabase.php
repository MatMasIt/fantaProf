<?php
use \Bramus\Ansi\Ansi;
use \Bramus\Ansi\Writers\StreamWriter;
use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class PrepareDatabase extends Test
{
    public function __construct()
    {
        parent::__construct("Prepare Database", "Backup the database and clean the testing site", true);
    }
    public function run(array $resultValues): bool
    {
        $this->ansi->text("Copying api/database.sqlite3 in api/database.sqlite3.backup")->nostyle()->lf();
        return copy("../api/database.sqlite3", "../api/database.sqlite3.backup");
    }
}
