======================================
Writing a Unit of Work in PHP Doctrine
======================================

:Authors: - Jon Lebensold
:Contact: http://jon.lebensold.ca/


In this tutorial, we're going to create a Unit Of Work object that will
simplify performing transactions with Doctrine Models. The Goal here is
to centralize all of our commits to the database into one class which
will perform them transactionally.

Afterwards, we can extend this class to include logging and error
handling in case a commit fails.

It is helpful to think of the Unit of Work as a way of putting
everything that we would want to update, insert and delete into one bag
before sending it to the database.

Let's create a Doctrine YAML file with a Project Model:

Project: tableName: lookup\_project columns: id: primary: true
autoincrement: true type: integer(4) name: string(255)

With Doctrine models, saving a Project should be as simple as this:

 $project = new Project(); $project->name = 'new project';
$project->save();

However, as soon as we want to perform database transactions or logging
becomes a requirement, having save(); statements all over the place can
create a lot of duplication.

To start with, let's create a UnitOfWork class:

 class UnitOfWork { protected $*createOrUpdateCollection = array();
protected $*deleteCollection = array(); }

Because Doctrine is clever enough to know when to UPDATE and when to
INSERT, we can combine those two operations in one collection. We'll
store all the delete's that we're planning to form in
$\_deleteCollection.

Now we need to add some code to our class to make sure the same object
isn't added twice.

 protected function
*existsInCollections(:code:`model) { // does the model already belong to the createOrUpdate collection? foreach (`\ this->*createOrUpdateCollection
as :code:`m) { if (`\ model->getOid() == $m->getOid()) { return true; }
}

::

    // does the model already belong to the delete collection?
    foreach ($this->_deleteCollection as $m) {
        if ($model->getOid() == $m->getOid()) {
            return true;
        }

}

return false; }

Now we can add our public methods that will be used by code outside of
the UnitOfWork:

 public function
registerModelForCreateOrUpdate(:code:`model) { // code to check to see if the model exists already if (`\ this->\_existsInCollections($model))
{ throw new Exception('model already in another collection for this
transaction'); }

::

    // no? add it
    $this->_createOrUpdateCollection[] = $model;

}

public function
registerModelForDelete(:code:`model) { // code to check to see if the model exists already if (`\ this->\_existsInCollections($model))
{ throw new Exception('model already in another collection for this
transaction'); }

::

    // no? add it
    $this->_deleteCollection[] = $model;

}

Before we write the transaction code, we should also be able to let
other code clear the Unit Of Work. We'll use this method internally as
well in order to flush the collections after our transaction is
succesful.

 public function clearAll() { $this->*deleteCollection = array();
$this->*createOrUpdateCollection = array(); }

With skeleton in place, we can now write the code that performs the
Doctrine transaction:

 public function commitAll() { $conn = Doctrine\_Manager::connection();

::

    try {
          $conn->beginTransaction();

          $this->_performCreatesOrUpdates($conn);
          $this->_performDeletes($conn);

          $conn->commit();
    } catch(Doctrine_Exception $e) {
        $conn->rollback();
    }

    $this->clearAll();

}

Now we're assuming that we've already started a Doctrine connection. In
order for this object to work, we need to initialize Doctrine. It's
often best to put this kind of code in a config.php file which is loaded
once using require\_once();

 define('SANDBOX\_PATH', dirname(**FILE**)); define('DOCTRINE\_PATH',
SANDBOX\_PATH . DIRECTORY\_SEPARATOR . 'lib'); define('MODELS\_PATH',
SANDBOX\_PATH . DIRECTORY\_SEPARATOR . 'models');
define('YAML\_SCHEMA\_PATH', SANDBOX\_PATH . DIRECTORY\_SEPARATOR .
'schema'); define('DB\_PATH', 'mysql://root:@localhost/database');

require\_once(DOCTRINE\_PATH . DIRECTORY\_SEPARATOR . 'Doctrine.php');

spl\_autoload\_register(array('Doctrine', 'autoload'));
Doctrine\_Manager::getInstance()->setAttribute(Doctrine\_Core::ATTR\_MODEL\_LOADING,
Doctrine\_Core::MODEL\_LOADING\_CONSERVATIVE);

$connection = Doctrine\_Manager::connection(DB\_PATH, 'main');

Doctrine\_Core::loadModels(MODELS\_PATH);

With all that done, we can now invoke the Unit of Work to perform a
whole range of operations in one clean transaction without adding
complexity to the rest of our code base.

 $t = Doctrine\_Core::getTable('Project'); $lastProjects =
$t->findByName('new project');

$unitOfWork = new UnitOfWork();

// prepare an UPDATE $lastProjects[0]->name = 'old project';
:code:`unitOfWork->registerModelForCreateOrUpdate(`\ lastProjects[0]);

// prepare a CREATE $project = new Project(); $project->name = 'new
project name';

:code:`unitOfWork->registerModelForCreateOrUpdate(`\ project);

// prepare a DELETE :code:`unitOfWork->registerModelForDelete(`\ lastProjects[3]);

// perform the transaction $unitOfWork->commitAll();

The end result should look like this:

 class UnitOfWork { /\*\* \* Collection of models to be persisted \* \*
@var array Doctrine\_Record \*/ protected $\_createOrUpdateCollection =
array();

::

    /**
     * Collection of models to be persisted
     *
     * @var array Doctrine_Record
     */
    protected $_deleteCollection = array();

    /**
     * Add a model object to the create collection
     *
     * @param Doctrine_Record $model
     */
    public function registerModelForCreateOrUpdate($model)
    {
        // code to check to see if the model exists already
        if ($this->_existsInCollections($model)) {
            throw new Exception('model already in another collection for this transaction');
        }

        // no? add it
        $this->_createOrUpdateCollection[] = $model;
    }

    /**
     * Add a model object to the delete collection
     *
     * @param Doctrine_Record $model
     */
    public function registerModelForDelete($model)
    {
          // code to check to see if the model exists already
          if ($this->_existsInCollections($model)) {
              throw new Exception('model already in another collection for this transaction');
          }

          // no? add it
          $this->_deleteCollection[] = $model;
    }

    /**
     * Clear the Unit of Work
     */
    public function ClearAll()
    {
        $this->_deleteCollection = array();
        $this->_createOrUpdateCollection = array();
    }

    /**
     * Perform a Commit and clear the Unit Of Work. Throw an Exception if it fails and roll back.
     */
    public function commitAll()
    {
        $conn = Doctrine_Manager::connection();

        try {
            $conn->beginTransaction();

            $this->performCreatesOrUpdates($conn);
            $this->performDeletes($conn);

            $conn->commit();
        } catch(Doctrine_Exception $e) {
            $conn->rollback();
        }

        $this->clearAll();
    }

    protected function _performCreatesOrUpdates($conn)
    {
        foreach ($this->_createOrUpdateCollection as $model) {
            $model->save($conn);
        }
    }

    protected function _performDeletes($conn)
    {
        foreach ($this->_deleteCollection as $model) {
            $model->delete($conn);
        }
    }

    protected function _existsInCollections($model)
    {
       foreach ($this->_createOrUpdateCollection as $m) {
            if ($model->getOid() == $m->getOid()) {
                return true;
            }
       }

       foreach ($this->_deleteCollection as $m) {
            if ($model->getOid() == $m->getOid()) {
                return true;
            }
       }

       return false;
    }

}

Thanks for reading, feel free to check out http://jon.lebensold.ca or
mail me at jon@lebensold.ca if you have any questions.
