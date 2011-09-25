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
 * Doctrine_Ticket_DC880_TestCase
 *
 * @package     Doctrine
 * @author      Andrew Coulton <andrew.coulton@proscenia.co.uk>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_DC880_TestCase extends Doctrine_UnitTestCase
{

    protected $driverName = 'Mysql';

    public function testMigrationWithBehavioursNotAddingUnexpectedChanges()
    {
        $manager = Doctrine_Manager::getInstance();

        $oldPrefix = $manager->getAttribute(Doctrine_Core::ATTR_MODEL_CLASS_PREFIX);
        $manager->setAttribute(Doctrine_Core::ATTR_MODEL_CLASS_PREFIX, 'Model_Test_DC880');

        $dir = dirname(__FILE__) . '/DC880/migrations';
        if (!is_dir($dir))
        {
            mkdir($dir, 0777, true);
        }
        $migration = new Doctrine_Migration($dir);

        $diff = new Doctrine_Migration_Diff(dirname(__FILE__) . '/DC880/dc880_schema.yml',
                                            dirname(__FILE__) . '/DC880/dc880_schema.yml',
                                            $migration);

        $changes = $diff->generateChanges();
        
        $foundChanges = array();
        foreach ($changes as $kind=>$models) {
            if (count($models)) {
                $foundChanges[$kind] = implode(',', array_keys($models));
            }
        }

        $this->assertEqual($foundChanges, array());

        $manager->setAttribute(Doctrine_Core::ATTR_MODEL_CLASS_PREFIX, $oldPrefix);
    }

}