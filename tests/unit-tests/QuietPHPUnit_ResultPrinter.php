<?php
class QuietPHPUnit_ResultPrinter extends PHPUnit_TextUI_ResultPrinter
{
  protected $outputBuffer = "";

  public function __construct($out = NULL, $verbose = FALSE, $colors = FALSE, $debug = FALSE)
  {
    parent::__construct($out, $verbose, $colors, $debug);
  }

  public function write($buffer)
  {
    if ($this->out) {
      fwrite($this->out, $buffer);

      if ($this->autoFlush) {
        $this->incrementalFlush();
      }
    } else {
      if (PHP_SAPI != 'cli') {
        $buffer = htmlspecialchars($buffer);
      }

      $this->outputBuffer .= $buffer;

      if ($this->autoFlush) {
        $this->incrementalFlush();
      }
    }
  }

  public function printResult(PHPUnit_Framework_TestResult $result)
  {

    parent::printResult($result);
    print $this->outputBuffer;
  }
}