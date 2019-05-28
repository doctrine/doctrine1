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
 * Doctrine_Ticket_OV10_TestCase
 *
 * proper fix for DC-962 (Broken logic when doctrine translates limit's into subqueries, with joins.)
 * https://web.archive.org/web/20130823211635/http://www.doctrine-project.org/jira/browse/DC-962
 *
 * Params duplicates for subquery should be inserted in correct place in params array.
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_OV10_TestCase extends Doctrine_UnitTestCase 
{
	public function testLimitSubqueryParams()
	{
		$profiler = new Doctrine_Connection_Profiler();
    	$this->conn->addListener($profiler);

		$q = Doctrine_Query::create()
			->select('u.id, "test?" as test, \'?test\' as test2')
			->from('User u')
			->leftJoin('u.Album a WITH user_id = ?', 1)
			->where('u.name = ?', 'test')
			->limit(10)
			->execute();

        $events = $profiler->getAll();
        do
		{
			$event = array_pop($events);
		} while ($event->getName() != 'execute');

		$this->assertEqual($event->getQuery(), 'SELECT e.id AS e__id, "test?" AS e__0, \'?test\' AS e__1 FROM entity e LEFT JOIN album a ON e.id = a.user_id AND (user_id = ?) WHERE e.id IN (SELECT DISTINCT e2.id FROM entity e2 LEFT JOIN album a2 ON e2.id = a2.user_id AND (user_id = ?) WHERE e2.name = ? AND (e2.type = 0) LIMIT 10) AND (e.name = ? AND (e.type = 0))');
		$this->assertEqual($event->getParams(), array(1, 1, 'test', 'test'));
	}
}