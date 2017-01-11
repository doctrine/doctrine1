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
 * Doctrine_Ticket_DC735_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_DC735_TestCase extends Doctrine_UnitTestCase 
{

    public function testImportSupportsSettersForRelatedModels() {
        $dbh = new Doctrine_Adapter_Mock('mysql');
        $conn = Doctrine_Manager::getInstance()->openConnection($dbh);

        Doctrine_Core::loadData(dirname(__FILE__) . '/DC735/fixtures.yml');

        $sql = $dbh->getAll();

        $this->assertEqual('INSERT INTO doctrine__ticket__d_c735__user (country_id) VALUES (?)', $sql[4]);
    }
}

class Doctrine_Ticket_DC735_Country extends Doctrine_Record {

    public function setTableDefinition() {
        $this->hasColumn('id', 'integer', 4);
        $this->hasColumn('name', 'string', 100);
    }

}

class Doctrine_Ticket_DC735_User extends Doctrine_Record {

    public function setTableDefinition() {
        $this->hasColumn('country_id', 'integer', 4);
    }

    public function setUp() {
        parent::setUp();
        $this->hasOne('Doctrine_Ticket_DC735_Country', array(
             'local' => 'country_id',
             'foreign' => 'id'));
    }

    public function setDoctrineTicketDC735Country($country) {
        return $this->_set('Doctrine_Ticket_DC735_Country', $country);
    }
}