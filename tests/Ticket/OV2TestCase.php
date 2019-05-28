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
 * Doctrine_Ticket_OV2_TestCase
 *
 * refactored link/unlink methods in Doctrine_Record - now they do not load whole relations before linking/unlinking
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_OV2_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = 'Ticket_OV2_User';
        $this->tables[] = 'Ticket_OV2_Role';
        $this->tables[] = 'Ticket_OV2_UserRole';
        $this->tables[] = 'Ticket_OV2_UserData';
        $this->tables[] = 'Ticket_OV2_Post';
        parent::prepareTables();
    }

    public function prepareData()
	{
		for ($i = 1; $i <= 3; $i++) {
            $user = new Ticket_OV2_User();
            $user->username = 'username'.$i;
            $user->password = 'password'.$i;
            $user->save();
            $user->free();
        }
		for ($i = 1; $i <= 5; $i++) {
            $role = new Ticket_OV2_Role();
            $role->name = 'role'.$i;
            $role->save();
            $role->free();
        }
		for ($i = 1; $i <= 5; $i++) {
            $post = new Ticket_OV2_Post();
            $post->content = 'content'.$i;
            $post->save();
            $post->free();
        }

        // save roles for first user
		foreach(array(2, 3, 4) as $roleId) {
			$ref = new Ticket_OV2_UserRole();
			$ref->id_user = 1;
			$ref->id_role = $roleId;
			$ref->save();
			$ref->free();
		}

		// save data for first user
		$data = new Ticket_OV2_UserData();
		$data->id_user = 1;
		$data->first_name = 'first_name';
		$data->last_name = 'last_name';
		$data->save();
		$data->free();
	}

    public function testLinkWithLoadedRelation()
    {
    	$user = Doctrine_Query::create()
    		->from('Ticket_OV2_User u')
    		->leftJoin('u.Roles r')
    		->where('u.id = ?', 1)
    		->fetchOne();

		$this->assertTrue($user->hasReference('Roles'));

		$user->link('Roles', array(1, 2, 3));
		$user->unlink('Roles', 4);

		$this->assertEqual($user->Roles->getLast()->id, 1);

		$user->free(true);
    }

    public function testLinkWithoutLoadedRelation()
    {
    	$user = Doctrine_Query::create()
    		->from('Ticket_OV2_User u')
    		->where('u.id = ?', 1)
    		->fetchOne();

		$user->link('Roles', array(1, 2, 3));
		$user->unlink('Roles', 4);

		$this->assertFalse($user->hasReference('Roles'));

		// should lazy-load the relation and apply modifications
		$this->assertEqual($user->Roles->getLast()->id, 1);
		$this->assertTrue($user->hasReference('Roles'));

		$user->free(true);
    }

    public function testLinkingOneToOne()
	{
		$user = Doctrine_Core::getTable('Ticket_OV2_User')->find(2);
		$user->link('Data', 1);

		$this->assertFalse($user->hasReference('Data'));
		$this->assertEqual($user->getPendingLinks(), array('Data' => array(1 => true)));

		$this->assertEqual($user->Data->first_name, 'first_name');
		$user->save();

		$user->free(true);

		$user = Doctrine_Core::getTable('Ticket_OV2_User')->find(1);
		$this->assertNull($user->Data->first_name);

		$user->free(true);
	}

	public function testLinkingOneToMany()
	{
		$user = Doctrine_Core::getTable('Ticket_OV2_User')->find(1);
		$user->link('Posts', array(1, 2));
		$this->assertEqual($user->Posts[1]->content, 'content2');
		$user->save();
		$user->free(true);
	}

	public function testFromArray()
	{
		$user = Doctrine_Query::create()
    		->from('Ticket_OV2_User u')
    		->leftJoin('u.Data d')
    		->leftJoin('u.Posts p')
    		->where('u.id = ?', 1)
    		->fetchOne();

		$user->fromArray(array(
			'Roles' => array(1, 2, 5),
			'Data' => array(1),
			'Posts' => array(), // unlink all
		));

		$this->assertEqual(array_values($user->Roles->toKeyValueArray('id', 'id')), array(2, 1, 5));
		$this->assertEqual($user->Data->first_name, 'first_name');
		$this->assertFalse($user->Posts->getFirst());
		$user->free(true);
	}

	public function testFromArray2()
	{
		$this->conn->clear();

		$post = Doctrine_Query::create()
    		->from('Ticket_OV2_Post p')
    		->innerJoin('p.User u')
    		->where('p.id = ?', 1)
    		->fetchOne();

		$post->fromArray(array(
			'User' => array(), // should not clear the user
		));

		$this->assertEqual($post->User->id, 1);
	}

    public function testUnlinkExcept()
    {
    	$user = Doctrine_Query::create()
    		->from('Ticket_OV2_User u')
    		->leftJoin('u.Roles r')
    		->where('u.id = ?', 1)
    		->fetchOne();

		$user->unlink('Roles', array(), false, array(2));
		$this->assertEqual($user->Roles->getLast()->id, 2);

		$user->free(true);
    }

    public function testUnlinkOneToOne()
	{
		$user = Doctrine_Core::getTable('Ticket_OV2_User')->find(2);
		$user->unlink('Data');
		$user->save();

		$user->free(true);

		$data = Doctrine_Core::getTable('Ticket_OV2_UserData')->find(1);
		$this->assertNull($data->id_user);

		$data->link('User', 1);
		$data->save();

		$this->assertEqual($data->id_user, 1);
	}

}

class Ticket_OV2_User extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('username', 'string', 64, array('notnull' => true));
		$this->hasColumn('password', 'string', 128, array('notnull' => true));
	}
	
	public function setUp()
	{
		// 1:1
		$this->hasOne('Ticket_OV2_UserData as Data', array('local' => 'id', 'foreign' => 'id_user'));
		// m:n
		$this->hasMany('Ticket_OV2_Role as Roles', array('local' => 'id_user', 'foreign' => 'id_role', 'refClass' => 'Ticket_OV2_UserRole'));
		// 1:n
		$this->hasMany('Ticket_OV2_Post as Posts', array('local' => 'id', 'foreign' => 'id_user'));
	}
}

class Ticket_OV2_UserData extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('id_user', 'integer');
		$this->hasColumn('first_name', 'string', 64);
		$this->hasColumn('last_name', 'string', 64);
	}

	public function setUp()
	{
		$this->hasOne('Ticket_OV2_User as User', array('local' => 'id_user', 'foreign' => 'id'));
	}
}

class Ticket_OV2_Post extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('id_user', 'integer');
		$this->hasColumn('content', 'string');
	}

	public function setUp()
	{
		// n:1
		$this->hasOne('Ticket_OV2_User as User', array('local' => 'id_user', 'foreign' => 'id'));
	}
}


class Ticket_OV2_Role extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('name', 'string', 64);
	}
	
	public function setUp()
	{
		$this->hasMany('Ticket_OV2_User as Users', array('local' => 'id_role', 'foreign' => 'id_user', 'refClass' => 'Ticket_OV2_UserRole'));
	}
}

class Ticket_OV2_UserRole extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->hasColumn('id_user', 'integer', null, array('primary' => true));
		$this->hasColumn('id_role', 'integer', null, array('primary' => true));
	}
	
	public function setUp()
	{
		$this->hasOne('Ticket_OV2_User as User', array('local' => 'id_user', 'foreign' => 'id', 'onDelete' => 'CASCADE'));
		$this->hasOne('Ticket_OV2_Role as Role', array('local' => 'id_role', 'foreign' => 'id', 'onDelete' => 'CASCADE'));
	}
}
