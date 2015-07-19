<?php

namespace Burroughs;

use DateTime;

class PayDateCalculatorTest extends \Codeception\TestCase\Test
{
   /**
    * @var \UnitTester
    */
    protected $tester;

    /**
     * @var PayDateCalculator
     */
    protected $calc;

    protected function _before()
    {
        // create a fresh calculator before each test
        $this->calc = new PayDateCalculator();
    }

    protected function _after()
    {
        // unset the calculator after each test
        unset($this->calc);
    }

    /**
     * Getting and setting the output filename
     */
    public function testGetAndSetFilename()
    {
        $this->assertTrue($this->calc->setOutputFile('test.txt'));
	    $this->assertEquals('test.txt',$this->calc->getOutputFile());
    }

    /**
     * We might want the output to display in the terminal too
     * Check we can set the flag
     */
    public function testgetAndSetDebugOutput()
    {
        // Should be false by default
        $this->assertFalse($this->calc->isDebugOutput());

        // Should be able to set and get true
        $this->assertTrue($this->calc->setDebugOutput(true));
        $this->assertTrue($this->calc->isDebugOutput());

        // Should be able to set and get false
        $this->assertTrue($this->calc->setDebugOutput(false));
        $this->assertFalse($this->calc->isDebugOutput());
    }

    /**
     *  By default we want to calculate 12 months
     *  But we might like to calculate more
     */
    public function getAndSetMonthsToCalculate()
    {
        $this->assertEquals(12,$this->calc->getMonthsToCalculate());
        $this->assertEquals(18,$this->calc->setMonthsToCalculate(18));
        $this->assertEquals(18,$this->calc->getMonthsToCalculate());
    }

    /**
     *  By default we start on the current month
     *  But we might like to calculate a different date range
     */
    public function getAndSetStartDate()
    {
        // check default
        $date = new DateTime();
        $start_date = $this->calc->getStartDate();
        $this->assertEquals($date->format('Y-m'),$start_date->format('Y-m'));

        //check custom start date
        $date = new DateTime('2016-02-17'); // happy birthday to me :-)
        $this->calc->setStartDate($date);
        $start_date = $this->calc->getStartDate();
        $this->assertEquals($date->format('Y-m'),$start_date->format('Y-m'));
    }


    /**
     * should be able to write a new file
     * and return the file object
     */
    public function testCanCreateFileWhenValidFilenameIsSet()
    {
        $this->calc->setOutputFile('test.txt');
        $this->assertTrue($this->invokeMethod($this->calc,'createFile'));
        $this->assertInstanceOf('SplFileObject',$this->calc->getOutputFile());
    }


    /**
     * should throw a flaky if it cant create the file for permission reasons
     */
    public function testThrowsExceptionWhenInvalidFilenameIsSet()
    {
        $this->calc->setOutputFile('/text_file_in_root.txt');
        $this->setExpectedException('Exception');
        $this->invokeMethod($this->calc,'createFile');
    }


    /**
     * should be able to write a new file
     * and return the file object
     */
    public function testThrowsExceptionWhenNoFilenameIsSet()
    {
        $this->setExpectedException('Exception');
        $this->invokeMethod($this->calc,'createFile');
    }

    /**
     * we should have an empty array by default
     */
    public function testGetResults()
    {
        $is_array = is_array($this->calc->getResults());
        $this->assertTrue($is_array);
    }

    /**
     *  There are 4 combinations of results for each month
     *  BonusOnWeekend | SalaryOnWeekend
     *        0        |       0
     *        0        |       1
     *        1        |       0
     *        1        |       1
     *
     *  We will test each of the 4 scenarios
     *
     *
     *  testing for a month where both the salary and bonus dates fall on a weekday
     *  0 | 0
     */
    public function TestCalculateMonthResultsWithPaydayAndBonusOnWeekday()
    {
        $date = new DateTime('2016-02-17'); // happy birthday to me :-)
        $this->calc->setStartDate($date);
        $result = $this->invokeMethod($this->calc,'calculateMonth',[$date]);

        // we should have an array with three keys
        $valid = (is_array($result) && array_key_exists('month',$result) && array_key_exists('salary_date',$result) && array_key_exists('bonus_date',$result));
        $this->assertTrue($valid);

        // Month should read February
        $this->assertEquals('February',$result['month']);

        // Salary Date should read 2016-02-28
        $this->assertEquals('2016-02-28',$result['salary_date']);

        // Bonus Date should read 2016-03-15
        $this->assertEquals('2016-03-15',$result['bonus_date']);
    }

    /**
     *  testing for a month where the salary and bonus dates fall on a weekend
     *  1 | 1
     */
    public function TestCalculateMonthResultsWithPaydayAndBonusOnWeekend()
    {
        $date = new DateTime('2015-02-17');
        $this->calc->setStartDate($date);
        $result = $this->invokeMethod($this->calc,'calculateMonth',[$date]);

        // we should have an array with three keys
        $valid = (is_array($result) && array_key_exists('month',$result) && array_key_exists('salary_date',$result) && array_key_exists('bonus_date',$result));
        $this->assertTrue($valid);

        // Month should read February
        $this->assertEquals('February',$result['month']);

        // Salary Date should read 2016-02-27 (the Friday)
        $this->assertEquals('2016-02-27',$result['salary_date']);

        // Bonus Date should read 2016-03-18 (the 15th is a Sunday)
        $this->assertEquals('2016-03-18',$result['bonus_date']);
    }

    /**
     *  testing for a month where only the salary falls on a weekend
     *  0 | 1
     */
    public function TestCalculateMonthResultsWithPaydayOnWeekend()
    {
        $date = new DateTime('2016-01-04');
        $this->calc->setStartDate($date);
        $result = $this->invokeMethod($this->calc,'calculateMonth',[$date]);

        // we should have an array with three keys
        $valid = (is_array($result) && array_key_exists('month',$result) && array_key_exists('salary_date',$result) && array_key_exists('bonus_date',$result));
        $this->assertTrue($valid);

        // Month should read February
        $this->assertEquals('January',$result['month']);

        // Salary Date should read 2015-01-27 (the Friday)
        $this->assertEquals('2016-01-27',$result['salary_date']);

        // Bonus Date should read 2016-02-15
        $this->assertEquals('2016-02-15',$result['bonus_date']);
    }

    /**
     *  testing for a month where only the bonus falls on a weekend
     *  1 | 0
     */
    public function TestCalculateMonthResultsWithBonusOnWeekend()
    {
        $date = new DateTime('2016-09-18');
        $this->calc->setStartDate($date);
        $result = $this->invokeMethod($this->calc,'calculateMonth',[$date]);

        // we should have an array with three keys
        $valid = (is_array($result) && array_key_exists('month',$result) && array_key_exists('salary_date',$result) && array_key_exists('bonus_date',$result));
        $this->assertTrue($valid);

        // Month should read February
        $this->assertEquals('September',$result['month']);

        // Salary Date should read 2015-01-30
        $this->assertEquals('2016-09-30',$result['salary_date']);

        // Bonus Date should read 2016-10-19 (the 15th is a Saturday)
        $this->assertEquals('2016-10-19',$result['bonus_date']);
    }

    /**
     * Test adding a result gets added to the results array
     */
    public function testAddResult()
    {
        $date = new DateTime('2016-09-18');
        $this->calc->setStartDate($date);
        $result = $this->invokeMethod($this->calc,'calculateMonth',[$date]);

        $this->assertTrue($this->invokeMethod($this->calc,'addResult',[$result]));
        $result = $this->calc->getResults();
        $this->assertTrue(is_array($result));
        $this->assertTrue(count($result) == 1);
        $this->assertTrue($result['month'] == 'September');
        $this->assertTrue($result['salary_date'] == '2016-09-30');
        $this->assertTrue($result['salary_date'] == '2016-10-19');
    }

    /**
     * Passing in our result should return true
     */
    public function testAddResultToFile()
    {
        $this->calc->setOutputFile('test.txt');
        $result = [
            'month' == 'September',
            'salary_date' => '2016-09-30',
            'bonus_date' => '2016-10-19'
        ];
        $this->assertTrue($this->invokeMethod($this->calc,'addResultToFile',[$result]));
    }

    /**
     * We should get our file by calling getOutputFile()
     */
    public function testGetOutputFile()
    {
        $this->calc->setOutputFile('test.txt');
        $result = [
            'month' == 'September',
            'salary_date' => '2016-09-30',
            'bonus_date' => '2016-10-19'
        ];
        $this->assertInstanceOf('SplFileObject',$this->calc->getOutputFile());
    }


    /**
     * This method allows us to test protected and private methods without
     * having to go through everything using public methods
     *
     * @param object &$object
     * @param string $methodName
     * @param array  $parameters
     *
     * @return mixed could return anything!.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

}
