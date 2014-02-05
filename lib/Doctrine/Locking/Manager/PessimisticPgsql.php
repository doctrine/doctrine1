<?php
/*
 *  $Id: Pessimistic.php 7490 2010-03-29 19:53:27Z jwage $
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
 * Offline locking of records comes in handy where you need to make sure that
 * a time-consuming task on a record or many records, which is spread over several
 * page requests can't be interfered by other users.
 *
 * @package     Doctrine
 * @subpackage  Locking
 * @link        www.doctrine-project.org
 * @author      Roman Borschel <roman@code-factory.org>
 * @author      Pierre Minnieur <pm@pierre-minnieur.de>
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since       1.0
 * @version     $Revision: 7490 $
 */
class Doctrine_Locking_Manager_PessimisticPgsql extends Doctrine_Locking_Manager_Pessimistic
{
    /**
     * Obtains a lock on a {@link Doctrine_Record}
     *
     * @param  Doctrine_Record $record     The record that has to be locked
     * @param  mixed           $userIdent  A unique identifier of the locking user
     * @return boolean  TRUE if the locking was successful, FALSE if another user
     *                  holds a lock on this record
     * @throws Doctrine_Locking_Exception  If the locking failed due to database errors
     */
    public function getLock(Doctrine_Record $record, $userIdent)
    {
        $objectType = $record->getTable()->getComponentName();
        $key        = $this->_getRecordIdentifier($record);

        $gotLock = false;
        $time = time();

        try {
            $dbh = $this->conn->getDbh();
            $this->conn->beginTransaction();

            $stmt = $dbh->prepare('INSERT INTO ' . $this->_lockTable
                                  . ' (object_type, object_key, user_ident, timestamp_obtained)'
                                  . ' VALUES (:object_type, :object_key, :user_ident, :ts_obtained)');

            $stmt->bindParam(':object_type', $objectType);
            $stmt->bindParam(':object_key', $key);
            $stmt->bindParam(':user_ident', $userIdent);
            $stmt->bindParam(':ts_obtained', $time);

            $dbh->exec("SAVEPOINT beforeInsert");
            try {
                $stmt->execute();
                $dbh->exec("RELEASE SAVEPOINT beforeInsert");
                $gotLock = true;

            // we catch an Exception here instead of PDOException since we might also be catching Doctrine_Exception
            } catch(Exception $pkviolation) {
              $dbh->exec("ROLLBACK TO SAVEPOINT beforeInsert");
                // PK violation occured => existing lock!
            }

            if ( ! $gotLock) {
                $lockingUserIdent = $this->_getLockingUserIdent($objectType, $key);
                if ($lockingUserIdent !== null && $lockingUserIdent == $userIdent) {
                    $gotLock = true; // The requesting user already has a lock
                    // Update timestamp
                    $stmt = $dbh->prepare('UPDATE ' . $this->_lockTable 
                                          . ' SET timestamp_obtained = :ts'
                                          . ' WHERE object_type = :object_type AND'
                                          . ' object_key  = :object_key  AND'
                                          . ' user_ident  = :user_ident');
                    $stmt->bindParam(':ts', $time);
                    $stmt->bindParam(':object_type', $objectType);
                    $stmt->bindParam(':object_key', $key);
                    $stmt->bindParam(':user_ident', $lockingUserIdent);
                    $stmt->execute();
                }
            }
            $this->conn->commit();
        } catch (Exception $pdoe) {
            $this->conn->rollback();
            throw new Doctrine_Locking_Exception($pdoe->getMessage());
        }

        return $gotLock;
    }

}
