<?php

class Doctrine_Ticket_DC676_TestCase extends Doctrine_UnitTestCase
{
    public function prepareTables()
    {
        $this->tables[] = "Ticket_DC676_Article";
        $this->tables[] = "Ticket_DC676_Author";
        parent::prepareTables();
    }

    public function prepareData()
    {
    }

    public function testInit()
    {
        Doctrine_Manager::getInstance()->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_ALL);
    }

    public function testSaveEmptyArticle()
    {
        try
        {
            $this->conn->beginTransaction();
          
            $article = new Ticket_DC676_Article();
            
            # do stupid things on Author
            if ($article->Author->nickname)
            {
              # foobar
            }
            
            $article->save();
            
            $this->conn->commit();
            
            $this->fail("Article could be saved in spite of empty title!");
        }
        catch (Exception $e)
        {
            $this->pass();
        } 
    }
}

class Ticket_DC676_Article extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             'length' => '4',
             ));
        $this->hasColumn('author_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('title', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             'notnull' => true, 
             ));
        $this->hasColumn('body', 'string', 5000, array(
             'type' => 'string',
             'length' => '5000',
             ));
    }

    public function setUp()
    {
        parent::setUp();

        $this->hasOne('Ticket_DC676_Author as Author', array(
             'local' => 'author_id',
             'foreign' => 'id'));
    }
    
    public function save(Doctrine_Connection $con = null)
    {
        if (is_null($con))
        {
            $con = $this->getTable()->getConnection();
        }
    
        try
        {
            $con->beginTransaction();
    
            parent::save($con);
    
            $con->commit();
        }
        catch (Doctrine_Exception $e)
        {
            $con->rollback();
            throw $e;
        }
    }
}

class Ticket_DC676_Author extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             'length' => '4',
             ));
        $this->hasColumn('nickname', 'string', 100, array(
             'type' => 'string',
             'length' => '100',
             'default' => 'foobar'
             ));
    }

    public function setUp()
    {
        parent::setUp();

        $this->hasMany('Ticket_DC676_Article as Articles', array(
             'local' => 'id',
             'foreign' => 'author_id'));
    }
    
    # the test case passes if you remove this save method with transaction
    public function save(Doctrine_Connection $con = null)
    {
        if (is_null($con))
        {
            $con = $this->getTable()->getConnection();
        }
    
        try
        {
            $con->beginTransaction();
    
            parent::save($con);
    
            $con->commit();
        }
        catch (Doctrine_Exception $e)
        {
            $con->rollback();
            throw $e;
        }
    }
}