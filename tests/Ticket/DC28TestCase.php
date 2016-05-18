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
 * Doctrine_Ticket_DC28_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_DC28_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = 'Ticket_DC28_Tree';
        parent::prepareTables();
    }

    public function testQuery()
    {
        try {
            $q = Doctrine_Query::create()
                ->select('a.id, t.lang')
                ->from('Ticket_DC28_Tree a')
                ->innerJoin('a.Translation t WITH t.name != ?', 'test')
                ;
            $q->execute();
            //echo $q->getSqlQuery().PHP_EOL;
            
            $this->assertEqual(
                $q->getSqlQuery(), 
                'SELECT t.id AS t__id, t2.id AS t2__id, t2.lang AS t2__lang '.
                'FROM ticket__d_c28__tree t '.
                'INNER JOIN ticket__d_c28__tree_translation t2 '.
                'ON ( t.id = t2.id ) AND (t2.name != ?)'
            );
            
            //echo $q->getSqlQuery().PHP_EOL;
            $tree_table = Doctrine_Core::getTable('Ticket_DC28_Tree');
            $tree = $tree_table->getTree();
            $tree->setBaseQuery($q);
            //echo $q->getSqlQuery().PHP_EOL;
            
            $this->assertEqual(
                $q->getSqlQuery(), 
                'SELECT t.id AS t__id, t.lft AS t__lft, t.rgt AS t__rgt, t.level AS t__level, t2.id AS t2__id, t2.lang AS t2__lang '.
                'FROM ticket__d_c28__tree t '.
                'INNER JOIN ticket__d_c28__tree_translation t2 '.
                'ON ( t.id = t2.id ) AND (t2.name != ?)'
            );
            
            //$this->pass();
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}

class Ticket_DC28_Tree extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 255);
    }

    public function setUp()
    {
        $i18n = new Doctrine_Template_I18n(array('fields' => array(0 => 'name')));
        $this->actAs($i18n);
        $this->actAs('NestedSet');
    }
}