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
 * Doctrine_Ticket_OV23_TestCase
 *
 * wrap count query with another query, if "having" is used not only on expressions,
 * but on fields as well, and when there is no "group by" in the query
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_OV23_TestCase extends Doctrine_UnitTestCase
{
    public function testCountQueryWithHaving()
    {
        // Change the quote identifier, this only works with quote identifier enabled, otherwise complex count queries + join + having will silently fail
        // moreover it only works for identifier quoted with backticks i.e. mysql-style
        // that's because there is a following regex pattern used '/`[a-z0-9_]+`\.`[a-z0-9_]+`/i'
        // in getCountSqlQuery :(
        $dbh = new Doctrine_Adapter_Mock('mysql');
        $conn = $this->manager->openConnection($dbh);
        $conn->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);

        $q = Doctrine_Query::create($conn)
            ->from('User u')
            ->select('u.*, p.*')
            ->addSelect('COALESCE(u.name, u.loginname) AS player_name')
            ->leftJoin('u.Phonenumber p')
            ->having('player_name = ? OR p.phonenumber = ?', array('test', 'test'));

        $this->assertEqual($q->getCountSqlQuery(),
            'SELECT COUNT(*) AS `num_results` FROM ('
                .'SELECT DISTINCT `dctrn_count_query`.`id` FROM ('
                    .'SELECT `e`.`id`, COALESCE(`e`.`name`, `e`.`loginname`) AS `e__0`, `p`.`phonenumber` '
                    .'FROM `entity` `e` LEFT JOIN `phonenumber` `p` ON `e`.`id` = `p`.`entity_id` '
                    .'WHERE (`e`.`type` = 0) '
                    .'HAVING (`e__0` = ? OR `p`.`phonenumber` = ?)'
                .') `dctrn_count_query`'
            .') `dctrn_count_query_wrap`');

        // close connection
        $this->manager->closeConnection($conn);
    }

}
