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
 * Doctrine_Ticket_DC651_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_DC651_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = 'Ticket_DC651_User';
        $this->tables[] = 'Ticket_DC651_Group';
        $this->tables[] = 'Ticket_DC651_UserGroup';
        parent::prepareTables();
    }
    
    public function testTest()
    {
        $query = Doctrine_Query::create()
            ->select('g.*')
            ->from('Ticket_DC651_Group g')
            ->leftJoin('g.users u WITH u.uid=?', 1);
        
        $this->assertEqual($query->getSqlQuery(), 'SELECT g.gid AS g__gid FROM groups g LEFT JOIN users_groups u2 ON (g.gid = u2.group_gid) LEFT JOIN users u ON u.uid = u2.user_id AND (u.uid = ?) ORDER BY u.uid');
    }
}

class Ticket_DC651_User extends Doctrine_Record 
{
    public function setTableDefinition() 
    {
        $this->setTableName('users');
        $this->hasColumn('uid', 'integer', null, array('primary' => true));
        $this->option('orderBy', 'uid');
    }

    public function setUp() 
    {
        $this->hasMany('Ticket_DC651_Group as groups', array('refClass' => 'Ticket_DC651_UserGroup', 'local' => 'user_uid', 'foreign' => 'group_id'));
    }
}

class Ticket_DC651_Group extends Doctrine_Record 
{
    public function setTableDefinition() 
    {
        $this->setTableName('groups');
        $this->hasColumn('gid', 'integer', null, array('primary' => true));
    }

    public function setUp() {
        $this->hasMany('Ticket_DC651_User as users', array('refClass' => 'Ticket_DC651_UserGroup', 'local' => 'group_gid', 'foreign' => 'user_id'));
    }
}

class Ticket_DC651_UserGroup extends Doctrine_Record 
{
    public function setTableDefinition() 
    {
        $this->setTableName('users_groups');
        $this->hasColumn('user_uid', 'integer', null, array('primary' => true));
        $this->hasColumn('group_gid', 'integer', null, array('primary' => true));
    }

    public function setUp() 
    {
        $this->hasOne('Ticket_DC651_User as user', array('local' => 'user_uid', 'foreign' => 'uid'));
        $this->hasOne('Ticket_DC651_Group as group', array('local' => 'group_gid', 'foreign' => 'gid'));
    }
}