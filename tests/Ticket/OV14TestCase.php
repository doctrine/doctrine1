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
 * Doctrine_Ticket_OV14_TestCase
 * Limit subquery
 * - added order by fields to select clause also for mysql, since it needs it since 5.7 (with ONLY_FULL_GROUP_BY enabled by default in sql-mode)
 * - add aliases to selected columns to avoid duplicate column error
 * - remember added columns to avoid duplicates + little refactor for optimization
 * - distinct with join and order by on joined column is not determinate, it must be wrapped with another subquery (and not only in oracle)
 * - changed alias - added _wrap_ to avoid conflicts with limit subquery ordered by joined column
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_OV14_TestCase extends Doctrine_UnitTestCase 
{
    public function testLimitSubqueryMysql()
    {
        $dbh = new Doctrine_Adapter_Mock('mysql');
        $conn = $this->manager->openConnection($dbh);

        $q = Doctrine_Query::create($conn)
            ->select('u.name, COUNT(u.id) count')
            ->from('User u')
            ->leftJoin('u.Phonenumber p')
            ->groupBy('u.name')
            ->orderBy('count DESC')
            ->limit(10);
        $this->assertEqual($q->getSqlQuery(), 'SELECT e.name AS e__name, COUNT(e.id) AS e__0 FROM entity e LEFT JOIN phonenumber p ON e.id = p.entity_id WHERE e.id IN (SELECT doctrine_subquery_wrap_alias.id FROM (SELECT DISTINCT e2.id, COUNT(e2.id) AS e2__0 FROM entity e2 LEFT JOIN phonenumber p2 ON e2.id = p2.entity_id WHERE (e2.type = 0) GROUP BY e2.name ORDER BY e2__0 DESC LIMIT 10) AS doctrine_subquery_wrap_alias) AND (e.type = 0) GROUP BY e.name ORDER BY e__0 DESC');

        $q = Doctrine_Query::create($conn)
            ->select('u.name, p.phonenumber')
            ->from('User u')
            ->leftJoin('u.Phonenumber p')
            ->orderBy('p.phonenumber')
            ->limit(10);
        $this->assertEqual($q->getSqlQuery(), 'SELECT e.id AS e__id, e.name AS e__name, p.id AS p__id, p.phonenumber AS p__phonenumber FROM entity e LEFT JOIN phonenumber p ON e.id = p.entity_id WHERE e.id IN (SELECT doctrine_subquery_wrap_alias.id FROM (SELECT DISTINCT doctrine_subquery_alias.id FROM (SELECT e2.id FROM entity e2 LEFT JOIN phonenumber p2 ON e2.id = p2.entity_id WHERE (e2.type = 0) ORDER BY p2.phonenumber) doctrine_subquery_alias LIMIT 10) AS doctrine_subquery_wrap_alias) AND (e.type = 0) ORDER BY p.phonenumber');

        $this->manager->closeConnection($conn);
    }

}