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
 * <http://www.phpdoctrine.org>.
 */

/**
 * Doctrine_Ticket_DC313_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_DC313_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = 'Ticket_DC313_BlogPost';
        $this->tables[] = 'Ticket_DC313_BlogCategory';
        $this->tables[] = 'Ticket_DC313_BlogPostCategory';
        parent::prepareTables();
    }
    
    public function testTest()
    {
        $query = Doctrine_Query::create()
            ->from('Ticket_DC313_BlogPost b')
            ->leftJoin('b.BlogCategories bc');
            
        $this->assertEqual($query->getSqlQuery(), 'SELECT t.id AS t__id, t.title AS t__title, t.content AS t__content, t2.id AS t2__id, t2.name AS t2__name FROM ticket__d_c313__blog_post t LEFT JOIN ticket__d_c313__blog_post_category t3 ON (t.id = t3.id_blog_post) LEFT JOIN ticket__d_c313__blog_category t2 ON t2.id = t3.id_blog_category ORDER BY t2.name');
    }
}

class Ticket_DC313_BlogPost extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('title', 'string', 128);
        $this->hasColumn('content', 'string');
    }
    
    public function setUp()
    {
        $this->hasMany('Ticket_DC313_BlogCategory as BlogCategories', array('local' => 'id_blog_post', 'foreign' => 'id_blog_category', 'refClass' => 'Ticket_DC313_BlogPostCategory', 'orderBy' => 'name'));
    }
}

class Ticket_DC313_BlogCategory extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 128);
    }
    
    public function setUp()
    {
        $this->hasMany('Ticket_DC313_BlogPost as BlogPosts', array('local' => 'id_blog_category', 'foreign' => 'id_blog_post', 'refClass' => 'Ticket_DC313_BlogPostCategory'));
    }
}

class Ticket_DC313_BlogPostCategory extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('id_blog_post', 'integer', null, array('primary' => true));
        $this->hasColumn('id_blog_category', 'integer', null, array('primary' => true));
    }
    
    public function setUp()
    {
        $this->hasOne('Ticket_DC313_BlogPost as BlogPost', array('local' => 'id_blog_post', 'foreign' => 'id', 'onDelete' => 'CASCADE'));
        $this->hasOne('Ticket_DC313_BlogCategory as BlogCategory', array('local' => 'id_blog_category', 'foreign' => 'id', 'onDelete' => 'CASCADE'));
    }
}