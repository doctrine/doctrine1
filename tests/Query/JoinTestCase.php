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
 * Doctrine_Query_JoinCondition_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Query_Join_TestCase extends Doctrine_UnitTestCase
{
    public function prepareTables()
    {
        $this->tables = array('Record_Country', 'Record_City', 'Record_District', 'Entity',
                              'User', 'Group', 'Email', 'Phonenumber', 'Groupuser', 'Account');

        parent::prepareTables();
    }
    public function prepareData()
    {
    }

    public function testInitData()
    {
        $c = new Record_Country();

        $c->name = 'Some country';

        $c->City[0]->name = 'City 1';
        $c->City[1]->name = 'City 2';
        $c->City[2]->name = 'City 3';

        $c->City[0]->District->name = 'District 1';
        $c->City[2]->District->name = 'District 2';
        
        $this->assertTrue(gettype($c->City[0]->District), 'object');
        $this->assertTrue(gettype($c->City[0]->District->name), 'string');

        $c->save();

        $this->connection->clear();
    }

    public function testQuerySupportsCustomJoins()
    {
        $q = new Doctrine_Query();

        $q->select('c.*, c2.*, d.*')
          ->from('Record_Country c')->innerJoin('c.City c2 ON c2.id = 2')
          ->where('c.id = ?', array(1));

        $this->assertEqual($q->getSqlQuery(), 'SELECT r.id AS r__id, r.name AS r__name, r2.id AS r2__id, r2.name AS r2__name, r2.country_id AS r2__country_id, r2.district_id AS r2__district_id FROM record__country r INNER JOIN record__city r2 ON (r2.id = 2) WHERE (r.id = ?)');
    }


    public function testQueryAggFunctionInJoins()
    {
        $q = new Doctrine_Query();

        $q->select('c.*, c2.*, d.*')
          ->from('Record_Country c')
          ->innerJoin('c.City c2 WITH LOWER(c2.name) LIKE LOWER(?)', array('city 1'))
          ->where('c.id = ?', array(1));

        $this->assertEqual($q->getSqlQuery(), 'SELECT r.id AS r__id, r.name AS r__name, r2.id AS r2__id, r2.name AS r2__name, r2.country_id AS r2__country_id, r2.district_id AS r2__district_id FROM record__country r INNER JOIN record__city r2 ON ( r.id = r2.country_id ) AND (LOWER(r2.name) LIKE LOWER(?)) WHERE (r.id = ?)');
    }

    public function testSubQueryInJoins()
    {
        try {
            $q = new Doctrine_Query();

            $q->from('Record_Country c')
              ->innerJoin('c.City c2 WITH (c2.name = ? OR c2.id IN (SELECT c3.id FROM Record_City c3 WHERE c3.id = ? OR c3.id = ?))');
            $sql = $q->getSqlQuery();
            $this->assertEqual($sql, 'SELECT r.id AS r__id, r.name AS r__name, r2.id AS r2__id, r2.name AS r2__name, r2.country_id AS r2__country_id, r2.district_id AS r2__district_id FROM record__country r INNER JOIN record__city r2 ON ( r.id = r2.country_id ) AND ((r2.name = ? OR r2.id IN (SELECT r3.id AS r3__id FROM record__city r3 WHERE (r3.id = ? OR r3.id = ?))))');

            $this->pass();
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testQueryMultipleAggFunctionInJoins()
    {
        $q = new Doctrine_Query();

        $q->select('c.*, c2.*, d.*')
          ->from('Record_Country c')
          ->innerJoin('c.City c2 WITH LOWER(UPPER(c2.name)) LIKE LOWER(?)', array('city 1'))
          ->where('c.id = ?', array(1));

        $this->assertEqual($q->getSqlQuery(), 'SELECT r.id AS r__id, r.name AS r__name, r2.id AS r2__id, r2.name AS r2__name, r2.country_id AS r2__country_id, r2.district_id AS r2__district_id FROM record__country r INNER JOIN record__city r2 ON ( r.id = r2.country_id ) AND (LOWER(UPPER(r2.name)) LIKE LOWER(?)) WHERE (r.id = ?)');
    }
    
    
    public function testQueryMultipleAggFunctionInJoins2()
    {
        $q = new Doctrine_Query();

        $q->select('c.*, c2.*, d.*')
          ->from('Record_Country c')
          ->innerJoin('c.City c2 WITH LOWER(UPPER(c2.name)) LIKE CONCAT(UPPER(?), UPPER(c2.name))', array('city 1'))
          ->where('c.id = ?', array(1));

        $this->assertEqual($q->getSqlQuery(), 'SELECT r.id AS r__id, r.name AS r__name, r2.id AS r2__id, r2.name AS r2__name, r2.country_id AS r2__country_id, r2.district_id AS r2__district_id FROM record__country r INNER JOIN record__city r2 ON ( r.id = r2.country_id ) AND (LOWER(UPPER(r2.name)) LIKE CONCAT(UPPER(?), UPPER(r2.name))) WHERE (r.id = ?)');
    }
    
    
    public function testQueryMultipleAggFunctionInJoins3()
    {
        $q = new Doctrine_Query();

        $q->select('c.*, c2.*, d.*')
          ->from('Record_Country c')
          ->innerJoin('c.City c2 WITH CONCAT(UPPER(c2.name), c2.name) LIKE UPPER(?)', array('CITY 1city 1'))
          ->where('c.id = ?', array(1));

        $this->assertEqual($q->getSqlQuery(), 'SELECT r.id AS r__id, r.name AS r__name, r2.id AS r2__id, r2.name AS r2__name, r2.country_id AS r2__country_id, r2.district_id AS r2__district_id FROM record__country r INNER JOIN record__city r2 ON ( r.id = r2.country_id ) AND (CONCAT(UPPER(r2.name), r2.name) LIKE UPPER(?)) WHERE (r.id = ?)');
    }


    public function testQueryWithInInsideJoins()
    {
        $q = new Doctrine_Query();

        $q->select('c.*, c2.*, d.*')
          ->from('Record_Country c')
          ->innerJoin('c.City c2 WITH c2.id IN (?, ?)', array(1, 2))
          ->where('c.id = ?', array(1));

        $this->assertEqual($q->getSqlQuery(), 'SELECT r.id AS r__id, r.name AS r__name, r2.id AS r2__id, r2.name AS r2__name, r2.country_id AS r2__country_id, r2.district_id AS r2__district_id FROM record__country r INNER JOIN record__city r2 ON ( r.id = r2.country_id ) AND (r2.id IN (?, ?)) WHERE (r.id = ?)');
    }


    public function testQuerySupportsCustomJoinsAndWithKeyword()
    {
        $q = new Doctrine_Query();

        $q->select('c.*, c2.*, d.*')
          ->from('Record_Country c')->innerJoin('c.City c2 WITH c2.id = 2')
          ->where('c.id = ?', array(1));

        $this->assertEqual($q->getSqlQuery(), 'SELECT r.id AS r__id, r.name AS r__name, r2.id AS r2__id, r2.name AS r2__name, r2.country_id AS r2__country_id, r2.district_id AS r2__district_id FROM record__country r INNER JOIN record__city r2 ON ( r.id = r2.country_id ) AND (r2.id = 2) WHERE (r.id = ?)');
    }

    public function testRecordHydrationWorksWithDeeplyNestedStructuresAndArrayFetching()
    {
        $q = new Doctrine_Query();

        $q->select('c.*, c2.*, d.*')
          ->from('Record_Country c')->leftJoin('c.City c2')->leftJoin('c2.District d')
          ->where('c.id = ?', array(1));

        $countries = $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $c = $countries[0];
        $this->assertEqual($c['City'][0]['name'], 'City 1');
        $this->assertEqual($c['City'][1]['name'], 'City 2');
        $this->assertEqual($c['City'][2]['name'], 'City 3');

        $this->assertEqual($c['City'][0]['District']['name'], 'District 1');
        $this->assertEqual($c['City'][2]['District']['name'], 'District 2');
    }

    public function testRecordHydrationWorksWithDeeplyNestedStructures()
    {
        $q = new Doctrine_Query();

        $q->select('c.*, c2.*, d.*')
          ->from('Record_Country c')->leftJoin('c.City c2')->leftJoin('c2.District d')
          ->where('c.id = ?', array(1));

        $this->assertEqual($q->getSqlQuery(), "SELECT r.id AS r__id, r.name AS r__name, r2.id AS r2__id, r2.name AS r2__name, r2.country_id AS r2__country_id, r2.district_id AS r2__district_id, r3.id AS r3__id, r3.name AS r3__name FROM record__country r LEFT JOIN record__city r2 ON r.id = r2.country_id LEFT JOIN record__district r3 ON r2.district_id = r3.id WHERE (r.id = ?)");

        $countries = $q->execute();

        $c = $countries[0];
        $this->assertEqual($c->City[0]->name, 'City 1');
        $this->assertEqual($c->City[1]->name, 'City 2');
        $this->assertEqual($c->City[2]->name, 'City 3');

        $this->assertEqual($c->City[0]->District->name, 'District 1');
        $this->assertEqual($c->City[2]->District->name, 'District 2');
    }

    public function testManyToManyJoinUsesProperTableAliases()
    {
        $q = new Doctrine_Query();

        $q->select('u.name')->from('User u INNER JOIN u.Group g');

        $this->assertEqual($q->getSqlQuery(), 'SELECT e.id AS e__id, e.name AS e__name FROM entity e INNER JOIN groupuser g ON (e.id = g.user_id) INNER JOIN entity e2 ON e2.id = g.group_id AND e2.type = 1 WHERE (e.type = 0)');
    }

    public function testSelfReferentialAssociationJoinsAreSupported()
    {
        $q = new Doctrine_Query();

        $q->select('e.name')->from('Entity e INNER JOIN e.Entity e2');

        $this->assertEqual($q->getSqlQuery(), 'SELECT e.id AS e__id, e.name AS e__name FROM entity e INNER JOIN entity_reference e3 ON (e.id = e3.entity1 OR e.id = e3.entity2) INNER JOIN entity e2 ON (e2.id = e3.entity2 OR e2.id = e3.entity1) AND e2.id != e.id');
    }

    public function testMultipleJoins()
    {
        $q = new Doctrine_Query();
        $q->select('u.id, g.id, e.id')->from('User u')
          ->leftJoin('u.Group g')->leftJoin('g.Email e');

        $this->assertEqual($q->getSqlQuery(), 'SELECT e.id AS e__id, e2.id AS e2__id, e3.id AS e3__id FROM entity e LEFT JOIN groupuser g ON (e.id = g.user_id) LEFT JOIN entity e2 ON e2.id = g.group_id AND e2.type = 1 LEFT JOIN email e3 ON e2.email_id = e3.id WHERE (e.type = 0)');
        try {
            $q->execute();
            $this->pass();
        } catch (Doctrine_Exception $e) {
            $this->fail();
        }
    }

    public function testMultipleJoins2()
    {
        $q = new Doctrine_Query();
        $q->select('u.id, g.id, e.id')->from('Group g')
          ->leftJoin('g.User u')->leftJoin('u.Account a');

        $this->assertEqual($q->getSqlQuery(), 'SELECT e.id AS e__id, e2.id AS e2__id FROM entity e LEFT JOIN groupuser g ON (e.id = g.group_id) LEFT JOIN entity e2 ON e2.id = g.user_id AND e2.type = 0 LEFT JOIN account a ON e2.id = a.entity_id WHERE (e.type = 1)');
        try {
            $q->execute();
            $this->pass();
        } catch (Doctrine_Exception $e) {
            $this->fail();
        }
    }

    public function testMapKeywordForQueryWithOneComponent()
    {
        $q = new Doctrine_Query();
        $coll = $q->from('Record_City c INDEXBY c.name')->fetchArray();
        
        $this->assertTrue(isset($coll['City 1']));
        $this->assertTrue(isset($coll['City 2']));
        $this->assertTrue(isset($coll['City 3']));
    }

    public function testMapKeywordSupportsJoins()
    {
        $q = new Doctrine_Query();
        $country = $q->from('Record_Country c LEFT JOIN c.City c2 INDEXBY c2.name')->fetchOne();
        $coll = $country->City;

        $this->assertTrue(isset($coll['City 1']));
        $this->assertTrue(isset($coll['City 2']));
        $this->assertTrue(isset($coll['City 3']));
        $this->assertEqual('name', $coll->getKeyColumn());
    }

    public function testMapKeywordThrowsExceptionOnNonExistentColumn()
    {
        try {
            $q = new Doctrine_Query();
            $country = $q->from('Record_Country c LEFT JOIN c.City c2 INDEXBY c2.unknown')->fetchOne();
        
            $this->fail();
        } catch (Doctrine_Query_Exception $e) {
            $this->pass();
        }
    }
}
