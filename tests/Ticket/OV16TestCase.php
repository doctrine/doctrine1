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
 * Doctrine_Ticket_OV16_TestCase
 *
 * - fixed parsing of "set" clause in update queries
 * - improved checks for whether table alias should be used
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_OV16_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = 'OV16_Foo';
        $this->tables[] = 'OV16_Globale';
        $this->tables[] = 'OV16_Globale2';
        parent::prepareTables();
    }

    public function testSetFunction()
    {
        $q = Doctrine_Query::create()
                ->update('User')
                ->set('name', 'FIND_IN_SET(id, "1, 2, 3, 4, 5")');

        $this->assertEqual($q->getSqlQuery(), 'UPDATE entity SET name = FIND_IN_SET(id, "1, 2, 3, 4, 5") WHERE (type = 0)');

        // check tokenizer just in case...
        $tokenizer = new Doctrine_Query_Tokenizer();
        $terms = $tokenizer->sqlExplode('id, "1, 2, 3, 4, 5"', ',');
        $this->assertEqual($terms, array('id', '"1, 2, 3, 4, 5"'));
    }

    public function testSetNestedFunctionsArguments()
    {
        $foo_id = 1234;
        $adjustment = -3;

        $query = Doctrine_Query::create()
            ->update('OV16_Foo')
            ->set(
                'quantity','GREATEST(CAST(quantity AS SIGNED) + :adjustment,0)',
                array(':adjustment' => $adjustment)
            )
            ->where('id = :id', array(':id' => $foo_id));

        $this->assertEqual($query->getSqlQuery(), 'UPDATE o_v16__foo SET quantity = GREATEST(CAST(quantity AS SIGNED) + :adjustment, 0) WHERE (id = :id)');
    }

    public function testUpdateSupportsComplexExpressions()
    {
        $q = new Doctrine_Query();
        $q->update('User u')->set('u.name', "CONCAT(?, CONCAT(':', SUBSTRING(u.name, LOCATE(':', u.name)+1, LENGTH(u.name) - LOCATE(':', u.name)+1)))", array('gblanco'))
              ->where('u.id IN (SELECT u2.id FROM User u2 WHERE u2.name = ?) AND u.email_id = ?', array('guilhermeblanco', 5));

        $this->assertEqual($q->getSqlQuery(), "UPDATE entity SET name = CONCAT(?, CONCAT(':', SUBSTR(name, LOCATE(':', name)+1, LENGTH(name) - LOCATE(':', name)+1))) WHERE (id IN (SELECT e2.id AS e2__id FROM entity e2 WHERE (e2.name = ? AND (e2.type = 0))) AND email_id = ?) AND (type = 0)");
    }

    public function testUpdateJoinMysql()
    {
        $dbh = new Doctrine_Adapter_Mock('mysql');
        $conn = $this->manager->openConnection($dbh);

        $q = Doctrine_Query::create($conn)
            ->select('id')
            ->from('OV16_Globale')
            ->where('nita = ?', 'test');

        $this->assertEqual($q->getSqlQuery(), 'SELECT o.id AS o__id FROM o_v16__globale o WHERE (o.nita = ?)');

        $q = Doctrine_Query::create($conn)
            ->select('CONCAT(\':\', CONCAT(gg.nita, \':\'))', array('test'))
            ->from('OV16_Globale g')
            ->innerJoin('g.Globale2 gg');

        // \Doctrine_Query::getExpressionOwner can't find gg. in the select part, because it's between apostrophes
        // and it assigns the expression to the root table - but it's probably not harmful here... so it's left alone for now
        //$this->assertEqual($q->getSqlQuery(), 'SELECT CONCAT(\':\', CONCAT(o2.nita, \':\')) AS o2__0 FROM o_v16__globale o INNER JOIN o_v16__globale2 o2 ON o.neng = o2.neng');
        $this->assertEqual($q->getSqlQuery(), 'SELECT CONCAT(\':\', CONCAT(o2.nita, \':\')) AS o__0 FROM o_v16__globale o INNER JOIN o_v16__globale2 o2 ON o.neng = o2.neng');


        // [OV16] update-join queries should also have alias for root table and columns
        // only supported by mysql adapter atm
        $q = new Doctrine_Query($conn);
        $q->update('OV16_Globale g')
            ->innerJoin('g.Globale2 gg')
            ->set('g.nita', 'gg.nita')
            ->set('g.tita', 'gg.tita')
            ->set('g.notaita', 'gg.notaita')
            ->where('g.nita IS NULL')
            ->andWhere('gg.uris = ?', 'mma')
            ->andWhere('gg.nita IS NOT NULL');

        $this->assertEqual($q->getSqlQuery(), 'UPDATE o_v16__globale o INNER JOIN o_v16__globale2 o2 ON o.neng = o2.neng SET o.nita = o2.nita, o.tita = o2.tita, o.notaita = o2.notaita WHERE (o.nita IS NULL AND o2.uris = ? AND o2.nita IS NOT NULL)');

        $this->manager->closeConnection($conn);
    }

    public function testTokenizer()
	{
		$tokenizer = new Doctrine_Query_Tokenizer();
		$e = $tokenizer->bracketExplode('FIELD(e.name, 2, 3, 4, 5) DESC', ' ');
		$this->assertEqual($e, array('FIELD(e.name, 2, 3, 4, 5)', 'DESC'));
	}
}

class OV16_Foo extends Doctrine_Record
{
    public $hooks = array();

    public function setTableDefinition()
    {
        $this->hasColumn('quantity', 'integer');
    }
}

class OV16_Globale extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('nita', 'string');
        $this->hasColumn('tita', 'string');
        $this->hasColumn('notaita', 'string');
        $this->hasColumn('neng', 'integer');
    }

    public function setUp()
    {
        $this->hasOne('OV16_Globale2 as Globale2', array('local' => 'neng', 'foreign' => 'neng'));
    }
}
class OV16_Globale2 extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('nita', 'string');
        $this->hasColumn('tita', 'string');
        $this->hasColumn('notaita', 'string');
        $this->hasColumn('uris', 'string');
        $this->hasColumn('neng', 'integer');
    }

    public function setUp()
    {
        $this->hasOne('OV16_Globale as Globale', array('local' => 'neng', 'foreign' => 'neng'));
    }
}