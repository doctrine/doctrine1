<?php
class Doctrine_Ticket_1783_TestCase extends Doctrine_UnitTestCase
{
	public function prepareTables() 
    {
        $this->tables[] = 'Ticket_1783';
        parent::prepareTables();
    }
	
    public function testValidateLargeIntegers()
    {
        $this->manager->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_ALL);        

        $test = new Ticket_1783();
        //$test->bigint = PHP_INT_MAX + 1;
        // modified by mh - fixed value for 64bit env
        $test->bigint = 2147483647 + 1;

        $this->assertTrue($test->isValid());

        $this->manager->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_NONE);
    }
}

class Ticket_1783 extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('bigint', 'integer', null, array('type' => 'integer', 'unsigned' => true));
    }
}