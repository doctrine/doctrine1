<?php
/*
 *  $Id: Association.php 7490 2010-03-29 19:53:27Z jwage $
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
 * Doctrine_Relation_Association    this class takes care of association mapping
 *                         (= many-to-many relationships, where the relationship is handled with an additional relational table
 *                         which holds 2 foreign keys)
 *
 *
 * @package     Doctrine
 * @subpackage  Relation
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision: 7490 $
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 */
class Doctrine_Relation_Association extends Doctrine_Relation
{
    /**
     * @return Doctrine_Table
     */
    public function getAssociationFactory()
    {
        return $this->definition['refTable'];
    }

    public function getAssociationTable()
    {
        return $this->definition['refTable'];
    }

    /**
     * getRelationDql
     *
     * @param integer $count
     * @return string
     */
    public function getRelationDql($count, $context = 'record')
    {
        // [OV1] modified
        $component = $this->getTable()->getComponentName();
        $refComponent = $this->definition['refTable']->getComponentName();
        $sub = substr(str_repeat("?, ", $count),0,-2);

        switch ($context) {
            case "record":
                $dql  = 'FROM ' . $component . '.' . $refComponent;
                $dql .= ' WHERE ' . $component . '.' . $refComponent . '.' . $this->getLocalRefColumnName() . ' IN (' . $sub . ')';
                $dql .= $this->getOrderBy($component, false, $component . '.' . $refComponent);
                break;
            case "collection":
                $dql  = 'FROM ' . $refComponent . '.' . $component;
                $dql .= ' WHERE ' . $refComponent . '.' . $this->getLocalRefColumnName() . ' IN (' . $sub . ')';
                $dql .= $this->getOrderBy($refComponent . '.' . $component, false, $refComponent);
                break;
        }

        return $dql;
    }

    // [OV1] added
    /**
     * Get the relationship orderby SQL/DQL
     *
     * @param string $alias        The alias to use
     * @param boolean $columnNames Whether or not to use column names instead of field names
     * @param string $refAlias     The alias to use for refTable
     * @return string $orderBy
     */
    public function getOrderBy($alias = null, $columnNames = false, $refAlias = null)
    {
        if ( ! $alias) {
           $alias = $this->getTable()->getComponentName();
        }

        $orderBy = null;
        $refTable = $this->definition['refTable'];
        if ( ! $refAlias) {
           $refAlias = $alias . '.' . $this->definition['refTable']->getComponentName();
        }

        if(!empty($this->definition['refOrderBy'])) {
            $orderBy = $refTable->processOrderBy($refAlias, $this->definition['refOrderBy'], $columnNames);
            if ($orderBy == $this->definition['refOrderBy']) {
                $orderBy = null;
            }
        } else {
            $orderBy = $refTable->getOrderByStatement($refAlias, $columnNames);
        }

        if (!$orderBy) {
            $orderBy = $this->getOrderByStatement($alias, $columnNames);
        }

        return $orderBy ? ' ORDER BY ' . $orderBy : '';
    }

	/**
     * getLocalRefColumnName
     * returns the column name of the local reference column
     */
    final public function getLocalRefColumnName()
    {
	    return $this->definition['refTable']->getColumnName($this->definition['local']);
    }

    /**
     * getLocalRefFieldName
     * returns the field name of the local reference column
     */
    final public function getLocalRefFieldName()
    {
	    return $this->definition['refTable']->getFieldName($this->definition['local']);
    }

    /**
     * getForeignRefColumnName
     * returns the column name of the foreign reference column
     */
    final public function getForeignRefColumnName()
    {
	    return $this->definition['refTable']->getColumnName($this->definition['foreign']);
    }

    /**
     * getForeignRefFieldName
     * returns the field name of the foreign reference column
     */
    final public function getForeignRefFieldName()
    {
	    return $this->definition['refTable']->getFieldName($this->definition['foreign']);
    }

    /**
     * fetchRelatedFor
     *
     * fetches a component related to given record
     *
     * @param Doctrine_Record $record
     * @return Doctrine_Record|Doctrine_Collection
     */
    public function fetchRelatedFor(Doctrine_Record $record)
    {
        $id = $record->getIncremented();
        if (empty($id) || ! $this->definition['table']->getAttribute(Doctrine_Core::ATTR_LOAD_REFERENCES)) {
            $coll = Doctrine_Collection::create($this->getTable());
        } else {
            $coll = $this->getTable()->getConnection()->query($this->getRelationDql(1), array($id));
        }
        $coll->setReference($record, $this);
        return $coll;
    }
}