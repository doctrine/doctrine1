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
class Doctrine_Query_JoinCondition_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareData() 
    { }

    public function prepareTables() 
    { }

    public function testJoinConditionsAreSupportedForOneToManyLeftJoins()
    {
        $q = new Doctrine_Query();
        
        $q->parseDqlQuery("SELECT u.name, p.id FROM User u LEFT JOIN u.Phonenumber p ON p.phonenumber = '123 123'");

        $this->assertEqual($q->getSqlQuery(), "SELECT e.id AS e__id, e.name AS e__name, p.id AS p__id FROM entity e LEFT JOIN phonenumber p ON (p.phonenumber = '123 123') WHERE (e.type = 0)");
    }

    public function testJoinConditionsAreSupportedForOneToManyInnerJoins()
    {
        $q = new Doctrine_Query();
        
        $q->parseDqlQuery("SELECT u.name, p.id FROM User u INNER JOIN u.Phonenumber p ON p.phonenumber = '123 123'");

        $this->assertEqual($q->getSqlQuery(), "SELECT e.id AS e__id, e.name AS e__name, p.id AS p__id FROM entity e INNER JOIN phonenumber p ON (p.phonenumber = '123 123') WHERE (e.type = 0)");
    }

    public function testJoinConditionsAreSupportedForManyToManyLeftJoins()
    {
        $q = new Doctrine_Query();
        
        $q->parseDqlQuery("SELECT u.name, g.id FROM User u LEFT JOIN u.Group g ON g.id > 2");

        $this->assertEqual($q->getSqlQuery(), "SELECT e.id AS e__id, e.name AS e__name, e2.id AS e2__id FROM entity e LEFT JOIN groupuser g ON (e.id = g.user_id) LEFT JOIN entity e2 ON (e2.id > 2) AND e2.type = 1 WHERE (e.type = 0)");
    }

    public function testJoinConditionsAreSupportedForManyToManyInnerJoins()
    {
        $q = new Doctrine_Query();
        
        $q->parseDqlQuery("SELECT u.name, g.id FROM User u INNER JOIN u.Group g ON g.id > 2");
    
        $this->assertEqual($q->getSqlQuery(), "SELECT e.id AS e__id, e.name AS e__name, e2.id AS e2__id FROM entity e INNER JOIN groupuser g ON (e.id = g.user_id) INNER JOIN entity e2 ON (e2.id > 2) AND e2.type = 1 WHERE (e.type = 0)");
    }

    public function testJoinConditionsWithClauseAndAliases()
    {
        $q = new Doctrine_Query();

        $q->parseDqlQuery("SELECT a.name, b.id FROM User a LEFT JOIN a.Phonenumber b ON a.name = b.phonenumber");

        $this->assertEqual($q->getSqlQuery(), "SELECT e.id AS e__id, e.name AS e__name, p.id AS p__id FROM entity e LEFT JOIN phonenumber p ON (e.name = p.phonenumber) WHERE (e.type = 0)");
    }

    public function testJoinWithConditionAndNotInClause()
    {
        // Related to ticket #1329
        $q = new Doctrine_Query();

        $q->parseDqlQuery("SELECT a.name, b.id FROM User a LEFT JOIN a.Phonenumber b WITH a.id NOT IN (1, 2, 3)");

        $this->assertEqual($q->getSqlQuery(), "SELECT e.id AS e__id, e.name AS e__name, p.id AS p__id FROM entity e LEFT JOIN phonenumber p ON ( e.id = p.entity_id ) AND (e.id NOT IN (1, 2, 3)) WHERE (e.type = 0)");
    }
}
