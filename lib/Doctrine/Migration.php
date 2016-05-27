<?php
/*
 *  $Id: Migration.php 1080 2007-02-10 18:17:08Z jwage $
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
 * Doctrine_Migration
 *
 * this class represents a database view
 *
 * @package     Doctrine
 * @subpackage  Migration
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision: 1080 $
 * @author      Jonathan H. Wage <jwage@mac.com>
 */
class Doctrine_Migration
{
    /**
     * @var Doctrine_Connection $_connection
     */
    protected $_migrationTableName = 'migration_versions',
              $_migrationTableCreated = false,
              $_connection,
              $_migrationClassesDirectory = array(),
              $_migrationClasses = array(),
              $_reflectionClass,
              $_errors = array(),
              $_process,
              $_migrationClassCount = 0;

    protected static $_migrationClassesForDirectories = array();

    /**
     * Specify the path to the directory with the migration classes.
     * The classes will be loaded and the migration table will be created if it
     * does not already exist
     *
     * @param string $directory The path to your migrations directory
     * @param mixed $connection The connection name or instance to use for this migration
     * @return void
     */
    public function __construct($directory = null, $connection = null)
    {
        $this->_reflectionClass = new ReflectionClass('Doctrine_Migration_Base');

        if (is_null($connection)) {
            $this->_connection = Doctrine_Manager::connection();
        } else {
            if (is_string($connection)) {
                $this->_connection = Doctrine_Manager::getInstance()
                    ->getConnection($connection);
            } else {
                $this->_connection = $connection;
            }
        }

        $this->_process = new Doctrine_Migration_Process($this);

        if ($directory != null) {
            $this->_migrationClassesDirectory = $directory;

            $this->loadMigrationClassesFromDirectory();
        }

        $this->_migrationClassCount = count($this->_migrationClasses);
    }

    public function getConnection()
    {
        return $this->_connection;
    }

    public function setConnection(Doctrine_Connection $conn)
    {
        $this->_connection = $conn;
    }

    /**
     * Get the migration classes directory
     *
     * @return string $migrationClassesDirectory
     */
    public function getMigrationClassesDirectory()
    {
        return $this->_migrationClassesDirectory;
    }

    /**
     * Get the table name for storing the version number for this migration instance
     *
     * @return string $migrationTableName
     */
    public function getTableName()
    {
        return $this->_migrationTableName;
    }

    /**
     * Set the table name for storing the version number for this migration instance
     *
     * @param string $tableName
     * @return void
     */
    public function setTableName($tableName)
    {
        $this->_migrationTableName = $this->_connection
                ->formatter->getTableName($tableName);
    }

    /**
     * Load migration classes from the passed directory. Any file found with a .php
     * extension will be passed to the loadMigrationClass()
     *
     * @param string $directory  Directory to load migration classes from
     * @return void
     */
    public function loadMigrationClassesFromDirectory($directory = null)
    {
        $directory = $directory ? $directory:$this->_migrationClassesDirectory;

        $classesToLoad = array();
        $classes = get_declared_classes();
        foreach ((array) $directory as $dir) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir),
                RecursiveIteratorIterator::LEAVES_ONLY);

            if (isset(self::$_migrationClassesForDirectories[$dir])) {
                foreach (self::$_migrationClassesForDirectories[$dir] as $num => $className) {
                    $this->_migrationClasses[$num] = $className;
                }
            }

            foreach ($it as $file) {
                $info = pathinfo($file->getFileName());
                if (isset($info['extension']) && $info['extension'] == 'php') {
                    require_once($file->getPathName());

                    $array = array_diff(get_declared_classes(), $classes);
                    $className = end($array);

                    if ($className) {
                        $e = explode('_', $file->getFileName());
                        $timestamp = $e[0];

                        $classesToLoad[$timestamp] = array('className' => $className, 'path' => $file->getPathName());
                    }
                }
            }
        }
        ksort($classesToLoad, SORT_NUMERIC);
        foreach ($classesToLoad as $key => $class) {
            $this->loadMigrationClass($key, $class['className'], $class['path']);
        }
    }

    /**
     * Load the specified migration class name in to this migration instances queue of
     * migration classes to execute. It must be a child of Doctrine_Migration in order
     * to be loaded.
     *
     * @param int $timestamp
     * @param string $name
     * @param string $path
     * @return void
     */
    public function loadMigrationClass($timestamp, $name, $path = null)
    {
        $class = new ReflectionClass($name);

        while ($class->isSubclassOf($this->_reflectionClass)) {

            $class = $class->getParentClass();
            if ($class === false) {
                break;
            }
        }

        if ($class === false) {
            return false;
        }

        $this->_migrationClasses[$timestamp] = $name;

        if ($path) {
            $dir = dirname($path);
            self::$_migrationClassesForDirectories[$dir][$timestamp] = $name;
        }
    }

    /**
     * Get all the loaded migration classes. Array where key is the number/version
     * and the value is the class name.
     *
     * @return array $migrationClasses
     */
    public function getMigrationClasses()
    {
        return $this->_migrationClasses;
    }

    /**
     * Add (up) a run migration to the migration table
     *
     * @param integer $timestamp
     * @return void
     */
    public function addMigration($timestamp, $class_name = null)
    {
        $this->_connection->exec("INSERT INTO " . $this->_migrationTableName . " (timestamp_value, class_name) VALUES ({$timestamp}, '{$class_name}')");
    }

    /**
     * Remove (down) a run migration to the migration table
     *
     * @param integer $timestamp
     * @return void
     */
    public function removeMigration($timestamp)
    {
        $this->_connection->exec("DELETE FROM " . $this->_migrationTableName . " WHERE timestamp_value = {$timestamp}");
    }



    /**
     * Get the current version of the database
     *
     * @return integer $timestamps
     */
    public function getCurrentMigrations()
    {
        $this->_createMigrationTable();

        $result = $this->_connection->fetchColumn("SELECT timestamp_value FROM " . $this->_migrationTableName);

        if(count($result) > 0) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * hReturns true/false for whether or not this database has been migrated in the past
     *
     * @return boolean $migrated
     */
    public function hasMigrated($timestamp = null)
    {
        $this->_createMigrationTable();

        $query = "SELECT timestamp_value FROM " . $this->_migrationTableName;
        if(!empty($timestamp)) {
            $query .= " WHERE timestamp_value = '" . $timestamp . "'";
        }
        $result = $this->_connection->fetchColumn($query);

        return isset($result[0]) ? true:false;
    }

    /**
     * Get the next incremented class version based on the loaded migration classes
     *
     * @return integer $nextMigrationClassVersion
     */
    public function getNextMigrationClassVersion()
    {
        if (empty($this->_migrationClasses)) {
            return 0;
        } else {
            return ++$this->_migrationClassCount;
        }
    }

    /**
     * Perform a migration process by specifying the migration number/version to
     * migrate to. It will automatically know whether you are migrating up or down
     * based on the current version of the database.
     *
     * @param  integer $to       Version to migrate to
     * @param  boolean $dryRun   Whether or not to run the migrate process as a dry run
     * @return integer $to       Version number migrated to
     * @throws Doctrine_Exception
     */
    public function migrate($to = null, $dryRun = false)
    {
        $this->clearErrors();

        $this->_createMigrationTable();

        $this->_connection->beginTransaction();

        try {
            $this->_doMigrate($to);
        } catch (Exception $e) {
            $this->addError($e);
        }

        if ($this->hasErrors()) {
            $this->_connection->rollback();

            if ($dryRun) {
                return false;
            } else {
                $this->_throwErrorsException();
            }
        } else {
            if ($dryRun) {
                $this->_connection->rollback();
                if ($this->hasErrors()) {
                    return false;
                } else {
                    return true;
                }
            } else {
                $this->_connection->commit();
                return true;
            }
        }
        return false;
    }

    /**
     * Run the migration process but rollback at the very end. Returns true or
     * false for whether or not the migration can be ran
     *
     * @param  string  $to
     * @return boolean $success
     */
    public function migrateDryRun($to = null)
    {
        return $this->migrate($to, true);
    }

    /**
     * Get the number of errors
     *
     * @return integer $numErrors
     */
    public function getNumErrors()
    {
        return count($this->_errors);
    }

    /**
     * Get all the error exceptions
     *
     * @return array $errors
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Clears the error exceptions
     *
     * @return void
     */
    public function clearErrors()
    {
        $this->_errors = array();
    }

    /**
     * Add an error to the stack. Excepts some type of Exception
     *
     * @param Exception $e
     * @return void
     */
    public function addError(Exception $e)
    {
        $this->_errors[] = $e;
    }

    /**
     * Whether or not the migration instance has errors
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return $this->getNumErrors() > 0 ? true:false;
    }

    /**
     * Get instance of migration class for number/version specified
     *
     * @param integer $num
     * @throws Doctrine_Migration_Exception $e
     */
    public function getMigrationClass($num)
    {
        if (isset($this->_migrationClasses[$num])) {
            $className = $this->_migrationClasses[$num];
            return new $className();
        }

        throw new Doctrine_Migration_Exception('Could not find migration class for migration step: '.$num);
    }

    /**
     * Throw an exception with all the errors trigged during the migration
     *
     * @return void
     * @throws Doctrine_Migration_Exception $e
     */
    protected function _throwErrorsException()
    {
        $messages = array();
        $num = 0;
        foreach ($this->getErrors() as $error) {
            $num++;
            $messages[] = ' Error #' . $num . ' - ' .$error->getMessage() . "\n" . $error->getTraceAsString() . "\n";
        }

        $title = $this->getNumErrors() . ' error(s) encountered during migration';
        $message  = $title . "\n";
        $message .= str_repeat('=', strlen($title)) . "\n";
        $message .= implode("\n", $messages);

        throw new Doctrine_Migration_Exception($message);
    }

    /**
     * Do the actual migration process
     *
     * @param  integer $to
     * @return integer $to
     * @throws Doctrine_Exception
     */
    protected function _doMigrate($to = null)
    {
        $query = "SELECT timestamp_value FROM ". $this->_migrationTableName . " ORDER BY timestamp_value ASC ";
        $pastMigrations = $this->_connection->fetchColumn($query);

        $notRunMigrations = array_diff(array_keys($this->_migrationClasses), $pastMigrations);

        if(in_array($to, $pastMigrations)) {
            $direction = 'down';
        } else {
            $direction = 'up';
        }

        if(!empty($notRunMigrations) && $direction === 'up') {
            // We have migrations that we haven't yet run

            // run them all
            foreach($notRunMigrations as $migrationKey) {
                $run = false;
                if(isset($to) && $migrationKey <= $to) {
                    $run = true;
                } else if (!isset($to)) {
                    $run = true;
                } else {
                    // skip
                }

                if($run) {
                    $this->_doMigrateStep('up', $migrationKey);
                    $this->addMigration($migrationKey, $this->_migrationClasses[$migrationKey]);
                }
            }
        } else if ($direction === 'down') {
            if(!isset($to)) {
                throw new Doctrine_migration_Exception('To go down, you must provide the timestamp of the migration!');
            }

            rsort($pastMigrations);

            foreach($pastMigrations as $migrationKey) {
                if($migrationKey > $to) {
                    $this->_doMigrateStep('down', $migrationKey);
                    $this->removeMigration($migrationKey);
                }
            }
        } else {
            throw new Doctrine_Migration_Exception('No new migrations to run');
        }

        return $to;
    }

    /**
     * Set migration versions as if everything is migrated
     *
     * @throws Doctrine_Exception
     */
    protected function assumeAllMigrated()
    {

        $notRunMigrations = array_keys($this->_migrationClasses);

        if(!empty($notRunMigrations)) {
            // We have migrations that we haven't yet run

            $migVer = $this->_connection->fetchColumn("SELECT version FROM migration_version");
            $migVer = isset($migVer[0]) ? $migVer[0]:0;

            $curVer = 0;

            // run them all
            foreach($notRunMigrations as $migrationKey) {
                if ($curVer >= $migVer) {
                    break;
                }

                $this->addMigration($migrationKey, $this->_migrationClasses[$migrationKey]);
                $curVer++;
            }
        }
    }

    /**
     * Perform a single migration step. Executes a single migration class and
     * processes the changes
     *
     * @param string $direction Direction to go, 'up' or 'down'
     * @param integer $num
     * @return void
     */
    protected function _doMigrateStep($direction, $num)
    {
        try {
            $migration = $this->getMigrationClass($num);

            $method = 'pre' . $direction;
            $migration->$method();

            if (method_exists($migration, $direction)) {
                $migration->$direction();
            } else if (method_exists($migration, 'migrate')) {
                $migration->migrate($direction);
            }

            if ($migration->getNumChanges() > 0) {
                $changes = $migration->getChanges();
                if ($direction == 'down' && method_exists($migration, 'migrate')) {
                    $changes = array_reverse($changes);
                }
                foreach ($changes as $value) {
                    list($type, $change) = $value;
                    $funcName = 'process' . Doctrine_Inflector::classify($type);
                    if (method_exists($this->_process, $funcName)) {
                        try {
                            $this->_process->$funcName($change);
                        } catch (Exception $e) {
                            $this->addError($e);
                        }
                    } else {
                        throw new Doctrine_Migration_Exception(sprintf('Invalid migration change type: %s', $type));
                    }
                }
            }

            $method = 'post' . $direction;
            $migration->$method();
        } catch (Exception $e) {
            $this->addError($e);
        }
    }

    /**
     * Create the migration table and return true. If it already exists it will
     * silence the exception and return false
     *
     * @return boolean $created Whether or not the table was created. Exceptions
     *                          are silenced when table already exists
     */
    protected function _createMigrationTable()
    {
        if ($this->_migrationTableCreated) {
            return true;
        }

        $this->_migrationTableCreated = true;

        try {
            $this->_connection->export->createTable($this->_migrationTableName,
                array(
                      'timestamp_value' => array('type' => 'integer', 'size' => 11),
                      'class_name' => array('type' => 'string', 'size' => '255')
                ));

            $this->assumeAllMigrated();

            return true;
        } catch(Exception $e) {
            return false;
        }
    }
}
