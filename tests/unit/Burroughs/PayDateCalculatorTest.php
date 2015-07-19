<?php

namespace Burroughs;

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

    }

    protected function _after()
    {
    }

    // tests
    public function testMe()
    {
        $this->calc = new PayDateCalculator();
	    $this->assertEquals('ok',$this->calc->hello());
    }

}
