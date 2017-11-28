<?php
/*
 *  $Id: Record.php 1298 2007-05-01 19:26:03Z zYne $
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
 * Doctrine_Record_Filter_Compound
 *
 * @package     Doctrine
 * @subpackage  Record
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision: 1298 $
 */
class Doctrine_Record_Filter_Compound extends Doctrine_Record_Filter
{
    protected $_aliases = array();

    public function __construct(array $aliases)
    {
        $this->_aliases = $aliases;
    }

    public function init()
    {
        // check that all aliases exist
        foreach ($this->_aliases as $alias) {
            $this->_table->getRelation($alias);
        }
    }

    /**
     * filterSet
     * defines an implementation for filtering the set() method of Doctrine_Record
     *
     * @param mixed $name                       name of the property or related component
     */
    public function filterSet(Doctrine_Record $record, $name, $value)
    {
        foreach ($this->_aliases as $alias) {
            if ( ! $record->exists()) {
                // modified for php7 where a "bug" with isset($record[$nested][$nested2]) was fixed
                // php5 did not check isset for $record[$nested] in this case, but retrieved it.
                // so in ArrayAccess which $record implements first offsetGet was called then offsetExists for $nested2
                // but php7 calls offsetExists for both. since relation for $alias is not loaded yet it returns false.
                // https://bugs.php.net/bug.php?id=69659
                // so, changed to keep old behavior which exploits the bug ;)
                //if (isset($record[$alias][$name])) {
                $relation = $record[$alias]; // force offsetGet
                if (isset($relation[$name])) { // here goes offsetExists
                    $record[$alias][$name] = $value;
                    return $record;
                }
            } else {
                // see above
                //if (isset($record[$alias][$name])) {
                $relation = $record[$alias]; // force offsetGet
                if (isset($relation[$name])) { // here goes offsetExists
                    $record[$alias][$name] = $value;
                }

                return $record;
            }
        }
        throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
    }

    /**
     * filterGet
     * defines an implementation for filtering the get() method of Doctrine_Record
     *
     * @param mixed $name                       name of the property or related component
     */
    public function filterGet(Doctrine_Record $record, $name)
    {
        foreach ($this->_aliases as $alias) {
            if ( ! $record->exists()) {
                // see above
                //if (isset($record[$alias][$name])) {
                $relation = $record[$alias]; // force offsetGet
                if (isset($relation[$name])) { // here goes offsetExists
                    return $record[$alias][$name];
                }
            } else {
                // see above
                //if (isset($record[$alias][$name])) {
                $relation = $record[$alias]; // force offsetGet
                if (isset($relation[$name])) { // here goes offsetExists
                    return $record[$alias][$name];
                }
            }
        }
        throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
    }
}