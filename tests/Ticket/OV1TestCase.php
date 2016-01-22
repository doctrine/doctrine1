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
 * Doctrine_Ticket_OV1_TestCase
 *
 * modified DC240 ticket for BC changes in Doctrine_Query - fixing orderBy in relations
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_OV1_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = 'Ticket_OV1_User';
        $this->tables[] = 'Ticket_OV1_Role';
        $this->tables[] = 'Ticket_OV1_UserRole';
        $this->tables[] = 'Ticket_OV1_RoleReference';
        parent::prepareTables();
    }

    public function testTest()
    {
        $q = Doctrine_Query::create()
        	->from('Ticket_OV1_User u')
        	->leftJoin('u.Roles r')
        	->leftJoin('r.Parents p')
        	->orderBy('username ASC');

        $this->assertEqual($q->getSqlQuery(), 'SELECT t.id AS t__id, t.username AS t__username, t.password AS t__password, t2.id AS t2__id, t2.name AS t2__name, t4.id AS t4__id, t4.name AS t4__name FROM ticket__o_v1__user t LEFT JOIN ticket__o_v1__user_role t3 ON (t.id = t3.id_user) LEFT JOIN ticket__o_v1__role t2 ON t2.id = t3.id_role LEFT JOIN ticket__o_v1__role_reference t5 ON (t2.id = t5.id_role_child) LEFT JOIN ticket__o_v1__role t4 ON t4.id = t5.id_role_parent ORDER BY t.username ASC, t3.position ASC, t5.position DESC');
    }

    public function test2Test()
    {
        $q = Doctrine_Query::create()
        	->from('Ticket_OV1_Role r')
        	->leftJoin('r.Users u');

        $this->assertEqual($q->getSqlQuery(), 'SELECT t.id AS t__id, t.name AS t__name, t2.id AS t2__id, t2.username AS t2__username, t2.password AS t2__password FROM ticket__o_v1__role t LEFT JOIN ticket__o_v1__user_role t3 ON (t.id = t3.id_role) LEFT JOIN ticket__o_v1__user t2 ON t2.id = t3.id_user ORDER BY t2.username');
    }

}

class Ticket_OV1_User extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('username', 'string', 64, array('notnull' => true));
		$this->hasColumn('password', 'string', 128, array('notnull' => true));
	}
	
	public function setUp()
	{
		$this->hasMany('Ticket_OV1_Role as Roles', array('local' => 'id_user', 'foreign' => 'id_role', 'refClass' => 'Ticket_OV1_UserRole', 'refOrderBy' => 'position ASC'));
	}
}

class Ticket_OV1_Role extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('name', 'string', 64);
	}
	
	public function setUp()
	{
		$this->hasMany('Ticket_OV1_User as Users', array('local' => 'id_role', 'foreign' => 'id_user', 'refClass' => 'Ticket_OV1_UserRole', 'orderBy' => 'username'));
		$this->hasMany('Ticket_OV1_Role as Parents', array('local' => 'id_role_child', 'foreign' => 'id_role_parent', 'refClass' => 'Ticket_OV1_RoleReference'));
		$this->hasMany('Ticket_OV1_Role as Children', array('local' => 'id_role_parent', 'foreign' => 'id_role_child', 'refClass' => 'Ticket_OV1_RoleReference'));
	}
}

class Ticket_OV1_UserRole extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('id_user', 'integer', null, array('primary' => true));
		$this->hasColumn('id_role', 'integer', null, array('primary' => true));
		$this->hasColumn('position', 'integer', null, array('notnull' => true));
	}
	
	public function setUp()
	{
		$this->hasOne('Ticket_OV1_User as User', array('local' => 'id_user', 'foreign' => 'id', 'onDelete' => 'CASCADE'));
		$this->hasOne('Ticket_OV1_Role as Role', array('local' => 'id_role', 'foreign' => 'id', 'onDelete' => 'CASCADE'));
	}
}

class Ticket_OV1_RoleReference extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('id_role_parent', 'integer', null, array('primary' => true));
		$this->hasColumn('id_role_child', 'integer', null, array('primary' => true));
		$this->hasColumn('position', 'integer', null, array('notnull' => true));

		$this->option('orderBy', 'position DESC');
	}
	
	public function setUp()
	{
		$this->hasOne('Ticket_OV1_Role as Parent', array('local' => 'id_role_parent', 'foreign' => 'id', 'onDelete' => 'CASCADE'));
		$this->hasOne('Ticket_OV1_Role as Child', array('local' => 'id_role_child', 'foreign' => 'id', 'onDelete' => 'CASCADE'));
	}
}