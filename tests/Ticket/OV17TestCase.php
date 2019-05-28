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
 * Doctrine_Ticket_OV17_TestCase
 *
 * Query:
 * - remember dependences on table aliases for sql parts
 * - remember dependences on join
 *
 * Other:
 * - optimizations, refactorings, commenting out unused code
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_OV17_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = 'Ticket_OV17_User';
        $this->tables[] = 'Ticket_OV17_Role';
        $this->tables[] = 'Ticket_OV17_UserRole';
        $this->tables[] = 'Ticket_OV17_UserData';
        $this->tables[] = 'Ticket_OV17_Player';
        $this->tables[] = 'Ticket_OV17_PlayerGroup';
        $this->tables[] = 'Ticket_OV17_Group';
        $this->tables[] = 'Ticket_OV17_Post';
        parent::prepareTables();
    }

    public function testAnalyzeDependences()
    {
        $q = Doctrine_Query::create()
            ->from('Ticket_OV17_User u')
            ->innerJoin('u.Data Data')
            ->leftJoin('u.Roles Roles')
            ->leftJoin('u.Player Player')
            ->leftJoin('Player.Groups PlayerGroups')
            ->leftJoin('u.Posts Posts');

        $this->assertEqual($q->getDependences(null, false), array(
            'select' => array(
                't' => true,
                't2' => true,
                't3' => true,
                't5' => true,
                't6' => true,
                't8' => true,
            ),
        ));
        $this->assertEqual($q->getAllJoinDependences(false), array(
            'Data' => array('t' => true),
            'Roles' => array('t4' => true),
            'Roles.Ticket_OV17_UserRole' => array('t' => true),
            'Player' => array ('t' => true),
            'PlayerGroups' => array('t7' => true),
            'PlayerGroups.Ticket_OV17_PlayerGroup' => array('t5' => true),
            'Posts' => array('t' => true),
        ));


        // add conditions
        $q->orderBy('u.username, Roles.name')
            ->where('PlayerGroups.name LIKE ?', 'Test%');

        $sql = $q->getSqlQuery(); // generating sql will trigger dependences analysis again
        $this->assertEqual($q->getDependences(null, false), array(
            'where' => array(
                't6' => true,
            ),
            'orderby' => array(
                't' => true,
                't3' => true,
            ),
            'select' => array(
                't' => true,
                't2' => true,
                't3' => true,
                't5' => true,
                't6' => true,
                't8' => true,
            ),
        ));

        $this->assertEqual($q->getDependencesMerged(array('where', 'orderby')), array('t6' => true, 't3' => true)); // without root (t)
    }

    // @todo analyze more queries
}


class Ticket_OV17_User extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('username', 'string', 64, array('notnull' => true));
		$this->hasColumn('password', 'string', 128, array('notnull' => true));
	}
	
	public function setUp()
	{
		// 1:1
		$this->hasOne('Ticket_OV17_UserData as Data', array('local' => 'id', 'foreign' => 'id_user'));
		// m:n
		$this->hasMany('Ticket_OV17_Role as Roles', array('local' => 'id_user', 'foreign' => 'id_role', 'refClass' => 'Ticket_OV17_UserRole'));
		// 1:n
		$this->hasMany('Ticket_OV17_Post as Posts', array('local' => 'id', 'foreign' => 'id_user'));
		// 1:1
		$this->hasOne('Ticket_OV17_Player as Player', array('local' => 'id', 'foreign' => 'id_user'));
	}
}

class Ticket_OV17_UserData extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('id_user', 'integer');
		$this->hasColumn('first_name', 'string', 64);
		$this->hasColumn('last_name', 'string', 64);
	}

	public function setUp()
	{
		$this->hasOne('Ticket_OV17_User as User', array('local' => 'id_user', 'foreign' => 'id'));
	}
}

class Ticket_OV17_Post extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('id_user', 'integer');
		$this->hasColumn('content', 'string');
	}

	public function setUp()
	{
		// n:1
		$this->hasOne('Ticket_OV17_User as User', array('local' => 'id_user', 'foreign' => 'id'));
	}
}


class Ticket_OV17_Role extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('name', 'string', 64);
	}
	
	public function setUp()
	{
		$this->hasMany('Ticket_OV17_User as Users', array('local' => 'id_role', 'foreign' => 'id_user', 'refClass' => 'Ticket_OV17_UserRole'));
	}
}

class Ticket_OV17_UserRole extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('id_user', 'integer', null, array('primary' => true));
		$this->hasColumn('id_role', 'integer', null, array('primary' => true));
	}
	
	public function setUp()
	{
		$this->hasOne('Ticket_OV17_User as User', array('local' => 'id_user', 'foreign' => 'id', 'onDelete' => 'CASCADE'));
		$this->hasOne('Ticket_OV17_Role as Role', array('local' => 'id_role', 'foreign' => 'id', 'onDelete' => 'CASCADE'));
	}
}


class Ticket_OV17_Player extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('id_user', 'integer');
		$this->hasColumn('player_name', 'string');
	}

	public function setUp()
	{
		// 1:1
		$this->hasOne('Ticket_OV17_User as User', array('local' => 'id_user', 'foreign' => 'id'));
		// m:n
		$this->hasMany('Ticket_OV17_Group as Groups', array('local' => 'id_player', 'foreign' => 'id_group', 'refClass' => 'Ticket_OV17_PlayerGroup'));
	}
}

class Ticket_OV17_Group extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('name', 'string', 64);
	}

	public function setUp()
	{
		$this->hasMany('Ticket_OV17_Player as Players', array('local' => 'id_group', 'foreign' => 'id_player', 'refClass' => 'Ticket_OV17_PlayerGroup'));
	}
}

class Ticket_OV17_PlayerGroup extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('id_player', 'integer', null, array('primary' => true));
		$this->hasColumn('id_group', 'integer', null, array('primary' => true));
	}

	public function setUp()
	{
		$this->hasOne('Ticket_OV17_Player as Player', array('local' => 'id_player', 'foreign' => 'id', 'onDelete' => 'CASCADE'));
		$this->hasOne('Ticket_OV17_Group as Group', array('local' => 'id_group', 'foreign' => 'id', 'onDelete' => 'CASCADE'));
	}
}
