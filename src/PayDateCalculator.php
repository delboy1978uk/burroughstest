<?php

namespace Burroughs;

use Exception;
use DateTime;
use SplFileObject;

class PayDateCalculator
{
    /** @var string $filename */
    private $filename;

    /** @var bool $debug */
    private $debug;

    /** @var DateTime $start_date */
    private $start_date;

    /** @var array $results_array */
    private $results_array;

    /** @var int $num_months */
    private $num_months;

    /** @var SplFileObject $output_file */
    private $output_file;

    public function __construct()
    {
        $this->debug = false;
        $this->start_date = new DateTime();
        $this->num_months = 12;
        $this->results_array = [];
    }

    /**
     * sets the filename
     * @param string $filename
     * @return PayDateCalculator
     */
    public function setOutputFile($filename)
    {
        $this->filename = (string) $filename;
        return $this;
    }

    /**
     *  returns the filename
     *  @return string
     */
    public function getOutputFile()
    {
        return $this->filename;
    }

    /**
     * @return bool
     */
    public function isDebugOutput()
    {
        return $this->debug;
    }

    /**
     * @param $bool
     * @return $this
     */
    public function setDebugOutput($bool)
    {
        $this->debug = $bool;
        return $this;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results_array;
    }

    /**
     * @param DateTime $date
     * @return $this
     */
    public function setStartDate(DateTime $date)
    {
        $this->start_date = $date;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * @param DateTime $date
     * @return array
     */
    public function calculateMonth(DateTime $date)
    {
        $month = $date->format('F');
        $salary = $this->calculateSalaryDay($date);
        $bonus = $this->calculateBonusDay($date);

        $result = [
            'month' => $month,
            'salary_date' => $salary->format('d M Y'),
            'bonus_date' => $bonus->format('d M Y'),
        ];
        return $result;
    }

    /**
     * @return int
     */
    public function getMonthsToCalculate()
    {
        return $this->num_months;
    }

    /**
     * @param $int
     * @return $this
     */
    public function setMonthsToCalculate($int)
    {
        $this->num_months = (int) $int;
        return  $this;
    }


    /**
     *  generates the results array and results file
     *  @return bool
     */
    public function generateResults()
    {
        $this->createFile($this->filename);
        $date = $this->start_date;
        while($this->num_months > 0)
        {
            $results = $this->calculateMonth($date);
            $this->addResult($results);
            $date->modify('+1 month');
            $this->num_months -- ;
        }
        return true;
    }

    /**
     * @param $date
     * @return DateTime
     */
    private function calculateSalaryDay($date)
    {
        $last_day = $date->format('t');
        $last = new DateTime($date->format('Y-m-'.$last_day.' H:i:s'));
        $day = $last->format('D');
        switch($day)
        {
            case 'Sat':
                $last->modify('-1 day');
                break;
            case 'Sun':
                $last->modify('-2 days');
                break;
            default:
                break;
        }
        return $last;
    }

    /**
     * @param DateTime $date
     * @return DateTime
     */
    private function calculateBonusDay(DateTime $date)
    {
        //set to 15th of next month
        $date->modify('+1 month');
        $date->setDate($date->format('Y'),$date->format('m'),15);
        $day = $date->format('D');
        switch($day)
        {
            case 'Sat':
                $date->modify('+4 day');
                break;
            case 'Sun':
                $date->modify('+3 days');
                break;
            default:
                break;
        }
        return $date;
    }

    /**
     * @param array $array
     * @return string
     */
    private function toCSV(array $array)
    {
        $csv = implode(',',$array);
        $csv .= "\n";
        return $csv;
    }

    /**
     * @throw Exception
     */
    private function createFile()
    {
        $this->output_file = new SplFileObject($this->filename,'w');
        return true;
    }

    private function addResult(array $result)
    {
        $this->results_array[] = $result;
        if(null !== $this->getOutputFile())
        {
            $this->addResultToFile($result);
        }
        return $this;
    }

    private function addResultToFile(array $result)
    {
        $csv = $this->toCSV($result);
        $this->output_file->eof();
        $this->output_file->fwrite($csv);
        return $this;

    }

    /**
     *  @return SplFileObject
     */
    public function getResultsFile()
    {
        return $this->output_file;
    }
}