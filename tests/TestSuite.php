<?php
require("vendor/autoload.php");

use \Bramus\Ansi\Ansi;
use \Bramus\Ansi\Writers\StreamWriter;
use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class TestSuite
{
    private Test $init, $cleanup;
    private array $tests;
    private Ansi $ansi;
    private string $title, $description;
    private array $resultsValues;
    public function __construct(string $title, string $description, Test $init, Test $cleanup, array $tests)
    {
        TestSuite::validateTestArray($tests);
        $this->title = $title;
        $this->description = $description;
        $this->init = $init;
        $this->cleanup = $cleanup;
        $this->tests = $tests;
        $this->ansi = new Ansi(new StreamWriter('php://stdout'));
        $this->resultsValues = [];
    }
    private static function validateTestArray(array $tests): void
    {
        foreach ($tests as $test) {
            if (!is_subclass_of($test, Test::class, false)) throw new TypeError();
        }
    }
    public function getInit(): Test
    {
        return $this->init;
    }
    public function setInit(Test $init): void
    {
        $this->init = $init;
    }
    public function getCleanup(): Test
    {
        return $this->cleanup;
    }
    public function setCleanup(Test $cleanup): void
    {
        $this->cleanup = $cleanup;
    }
    public function setTests(array $tests): void
    {
        TestSuite::validateTestArray($tests);
        $this->tests = $tests;
    }
    public function getTests(): array
    {
        return $this->tests;
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
    public function run(): bool
    {
        $this->ansi->eraseDisplay();
        $this->ansi->bold()->underline()->text($this->getTitle())->nostyle()->lf();
        $this->ansi->bold()->underline()->text($this->getDescription())->nostyle()->lf()->lf();
        $allPassed = true;
        $iRun = $this->init->run($this->resultsValues);
        $this->fancyPrintTest($this->init, $iRun);
        $i = 0;
        if ($iRun) {
            foreach ($this->tests as $test) {
                $testResult = $test->run($this->resultsValues);
                $this->fancyPrintTest($test, $testResult, $i);
                $this->resultsValues[$i] = $test->getResultValues();
                if (!$testResult) {
                    $allPassed = false;
                    break;
                }
                $i++;
            }
        } else {
            $allPassed = false;
        }
        if ($allPassed) {
            $this->ansi->lf()->color([SGR::COLOR_BG_GREEN, SGR::COLOR_FG_WHITE])
                ->text('Suite PASSED')
                ->nostyle()->lf()->lf();
        } else {
            if ($iRun) {
                $this->ansi->lf()->blink()->color([SGR::COLOR_BG_RED, SGR::COLOR_FG_WHITE])
                    ->text('Suite FAILED at test ' . ($i + 1))
                    ->nostyle()->lf()->lf();
            } else {

                $this->ansi->lf()->blink()->color([SGR::COLOR_BG_RED, SGR::COLOR_FG_WHITE])
                    ->text('Suite FAILED at INIT')
                    ->nostyle()->lf()->lf();
            }
        }
        $this->fancyPrintTest($this->cleanup, $this->cleanup->run($this->resultsValues), -2);

        return $allPassed;
    }

    /**
     * Prints a fancy box with test results
     *
     * @param Test $test
     * @param boolean $result
     * @param integer $index
     * @return void
     */
    private function fancyPrintTest(Test $test, bool $result, int $index = -1): void // -1 init, -2 end
    {
        if ($index > -1) {
            $this->ansi->bold()->underline()->text("[".date("d/m/Y H:i:s")."] Test " . ($index + 1) . " of " . count($this->tests))->nostyle()->lf();
        } elseif ($index == -1) {
            $this->ansi->bold()->underline()->text('Preparatory task')->nostyle()->lf();
        } else {
            $this->ansi->bold()->underline()->text('Cleanup task')->nostyle()->lf();
        }

        $this->ansi->italic()->text($test->getTitle())->nostyle()->lf();
        $this->ansi->italic()->text($test->getDescription())->nostyle()->lf()->lf();


        $test->printLog();

        if (!$result) {
            $this->ansi->color(SGR::COLOR_FG_RED)
                ->text('FAIL')
                ->nostyle()->lf()->bell()->lf();
        } else {
            $this->ansi->color(SGR::COLOR_FG_GREEN)
                ->text('PASS')
                ->nostyle()->lf()->lf();
        }
    }
}
