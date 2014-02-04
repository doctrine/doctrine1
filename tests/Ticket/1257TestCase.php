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
 * Doctrine_Ticket_1257_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_1257_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = 'Ticket_1257_User';
        $this->tables[] = 'Ticket_1257_Role';
        parent::prepareTables();
    }

    public function testTicket()
    {
        $user = new Ticket_1257_User();
        $user->username = 'jwage';
        $user->Role->name = 'Developer';
        $user->Role->description = 'Programmer/Developer';
        $user->save();

        $q = Doctrine_Query::create()
                ->select('u.id, u.username')
                ->from('Ticket_1257_User u')
                ->leftJoin('u.Role r')
                ->addSelect('r.id, r.name, r.description');
        $this->assertEqual($q->getSqlQuery(), 'SELECT t.id AS t__id, t.username AS t__username, t2.description AS t2__description, t2.id AS t2__id, t2.name AS t2__name FROM ticket_1257__user t LEFT JOIN ticket_1257__role t2 ON t.role_id = t2.id');
        $results = $q->fetchArray();
        $this->assertEqual($results[0]['Role']['name'], 'Developer');
    }
}

class Ticket_1257_User extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('username', 'string', 255);
        $this->hasColumn('password', 'string', 255);
        $this->hasColumn('role_id', 'integer');
    }

    public function setUp()
    {
        $this->hasOne('Ticket_1257_Role as Role', array('local' => 'role_id', 'foreign' => 'id'));
    }
}

class Ticket_1257_Role extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 255);
        $this->hasColumn('description', 'string');
    }

    public function setUp()
    {
        $this->hasMany('Ticket_1257_User as Users', array('local' => 'id', 'foreign' => 'role_id'));
    }
}