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
 * Doctrine_Ticket_OV4_TestCase
 *
 * added postSaveRelated hook to records
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_OV4_TestCase extends Doctrine_UnitTestCase
{
    public function prepareTables()
    {
        $this->tables[] = 'Ticket_OV4_User';
        $this->tables[] = 'Ticket_OV4_UserData';
        parent::prepareTables();
    }

    public function testAccessorForMappedValue()
    {
        $user = new Ticket_OV4_User();
        $user->hooks = array(
            'postSave' => null,
            'postRelatedSave' => null,
        );

        $user->username = 'test';
        $user->password = 'test';
        $user->Data->first_name = 'first_name';
        $user->save();

        $this->assertEqual($user->hooks['postSave'], false); // relations are not saved in postSave
        $this->assertEqual($user->hooks['postRelatedSave'], true); // added postRelatedSave hook, where relations should be saved already
    }
}

class Ticket_OV4_User extends Doctrine_Record
{
    public $hooks = array();

    public function setTableDefinition()
    {
        $this->hasColumn('username', 'string', 64, array('notnull' => true));
        $this->hasColumn('password', 'string', 128, array('notnull' => true));
    }

    public function setUp()
    {
        $this->hasOne('Ticket_OV4_UserData as Data', array('local' => 'id', 'foreign' => 'id_user'));
    }

    public function postSave($event)
    {
        $this->hooks[__FUNCTION__] = $this->Data->exists();
    }

    public function postRelatedSave($event)
    {
        $this->hooks[__FUNCTION__] = $this->Data->exists();
    }
}

class Ticket_OV4_UserData extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('id_user', 'integer');
        $this->hasColumn('first_name', 'string', 64);
        $this->hasColumn('last_name', 'string', 64);
    }

    public function setUp()
    {
        $this->hasOne('Ticket_OV4_User as User', array('local' => 'id_user', 'foreign' => 'id'));
    }
}
