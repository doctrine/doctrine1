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
 * Doctrine_Ticket_DC1036_TestCase
 *
 * @package     Doctrine
 * @author      John Kary <john@johnkary.net>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_DC1036_TestCase extends Doctrine_UnitTestCase
{
    private $tableName = 'mytable';

    public function testOracleAlterTableWithQuoteIdentifier()
    {
        $dbh = new Doctrine_Adapter_Mock('oracle');
        $conn = Doctrine_Manager::getInstance()->connection($dbh, 'DC1036', false);
        $conn->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);

        $fields = array(
            'username' => array(
                'type' => 'string',
                'length' => 100,
            ),
        );
        $conn->export->createTable($this->tableName, $fields);
        
        $this->assertEqual($dbh->pop(), 'COMMIT');
        $this->assertEqual($dbh->pop(), 'CREATE TABLE "' . $this->tableName . '" ("username" VARCHAR2(100))');
        $this->assertEqual($dbh->pop(), 'BEGIN TRANSACTION');

        $changes = array(
            'change' => array(
                'username' => array(
                    'definition' => array(
                        'type' => 'string',
                        'length' => 200,
                    ),
                ),
            ),
        );

        $conn->export->alterTable($this->tableName, $changes);
        $this->assertEqual($dbh->pop(), 'ALTER TABLE "' . $this->tableName . '" MODIFY ("username" VARCHAR2(200))');
    }
}
