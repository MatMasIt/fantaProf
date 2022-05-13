<?php

use \Bramus\Ansi\Ansi;
use \Bramus\Ansi\Writers\StreamWriter;
use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class RestoreDatabase extends Test
{
    public function __construct()

    {
        parent::__construct("Restore Database", "Restore the pre-test database", true);
    }
    public function run(array $resultValues): bool
    {
        $this->ansi->text("Copying api/database.sqlite3.backup back into api/database.sqlite3")->nostyle()->lf();
        if (!copy("../api/database.sqlite3.backup", "../api/database.sqlite3")) {
            $this->ansi->color(SGR::COLOR_FG_RED)
                ->text('Restore error')
                ->nostyle()->lf()->bell()->lf();
            return false;
        }
        if (!unlink("../api/database.sqlite3.backup")) {
            $this->ansi->color(SGR::COLOR_FG_ORANGE)
                ->text('Could not delete backup')
                ->nostyle()->lf()->bell()->lf();
            return false;
        }
        return true;
    }
}
