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
 * Doctrine_Ticket_OV15_TestCase
 * processPendingFields - do not auto-add primary key fields when group by is used (on other columns)
 *                (conform with mysql 5.7 default settings - all non-aggregated columns must be also in group by clause)
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_OV15_TestCase extends Doctrine_UnitTestCase 
{
    public function testTest()
    {
        $q = Doctrine_Query::create()
            ->select('u.name, COUNT(u.id) count')->from('User u')->groupBy('u.name');

        $this->assertEqual($q->getSqlQuery(), 'SELECT e.name AS e__name, COUNT(e.id) AS e__0 FROM entity e WHERE (e.type = 0) GROUP BY e.name');
    }

    public function test2Test()
    {
        // id column will be added to select, because it's a primary key and is in group by clause
        $q = Doctrine_Query::create()
            ->select('u.name, COUNT(u.updated) count')->from('User u')->groupBy('u.id, u.name');

        $this->assertEqual($q->getSqlQuery(), 'SELECT e.id AS e__id, e.name AS e__name, COUNT(e.updated) AS e__0 FROM entity e WHERE (e.type = 0) GROUP BY e.id, e.name');
    }

}