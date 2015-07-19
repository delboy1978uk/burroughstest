<?php

namespace Burroughs;

use Exception;
use DateTime;

class PayDateCalculator
{
    /** @var string $filename */
    private $filename;

    /** @var bool $debug */
    private $debug;

    /** @var DateTime $start_date */
    private $start_date;

    public function __construct()
    {
        $this->debug = false;
        $this->start_date = new DateTime();
    }

    /**
     * @param string $filename
     * @return PayDateCalculator
     */
    public function setOutputFile($filename)
    {
        $this->filename = (string) $filename;
        return $this;
    }

    public function getOutputFile()
    {
        return $this->filename;
    }

    public function isDebugOutput()
    {
        return $this->debug;
    }

    public function setDebugOutput($bool)
    {
        $this->debug = $bool;
        return $this;
    }

    public function getResults()
    {
        return 'this will fail';
    }

    public function setStartDate(DateTime $date)
    {
        $this->start_date = $date;
        return $this;
    }

    public function getStartDate()
    {
        return $this->start_date;
    }

    public function calculateMonth()
    {
        return 'this will fail';
    }

    private function createFile()
    {
        return 'this will fail';
    }

    private function addResult()
    {
        return 'this will fail';

    }

    private function addResultToFile()
    {
        return 'this will fail';

    }
}