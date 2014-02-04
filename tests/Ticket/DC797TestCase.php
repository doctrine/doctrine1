<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

/**
 * Doctrine_Ticket_DC797_TestCase
 *
 * Tests hydration of (optional) 1-to-1 relations, specifically whether they 
 * are hydrated in a clean state
 *
 * @package     Doctrine
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_DC797_TestCase extends Doctrine_UnitTestCase
{
    public function prepareData()
    {
        $foo = new DC797_Foo();
        $foo->save();
    }

    public function prepareTables()
    {
        $this->tables = array('DC797_Foo', 'DC797_Bar');
        parent::prepareTables();
    }

    public function testOneToOneHydrationDoesNotMarkResultModified()
    {
        $collection = Doctrine_Query::create()->from('DC797_Foo f')->leftJoin('f.DC797_Bar b')->execute();
        
        $this->assertEqual($collection->isModified(), false);
        $this->assertEqual($collection[0]->isModified(), false);
    }
}

class DC797_Foo extends Doctrine_Record
{
    public function setTableDefinition()
    {
       $this->hasColumn('id', 'integer', 4, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true,
            'unsigned' => true,
            'length' => 4,
       ));
       $this->hasColumn('bar_id', 'integer', 4, array(
            'type' => 'integer',
            'notnull' => false,
            'unsigned' => true,
            'length' => 4,
       ));
    }

    public function setUp()
    {
        parent::setUp();

        $this->hasOne('DC797_Bar', array(
            'local' => 'bar_id',
            'foreign' => 'id'
        ));
    }
}

class DC797_Bar extends Doctrine_Record
{
    public function setTableDefinition()
    {
       $this->hasColumn('id', 'integer', 4, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true,
            'unsigned' => true,
            'length' => 4,
       ));
    }

    public function setUp()
    {
        parent::setUp();

        $this->hasOne('DC797_Foo', array(
            'local' => 'id',
            'foreign' => 'bar_id'
        ));
    }
}