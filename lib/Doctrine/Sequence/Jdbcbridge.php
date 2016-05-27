<?php

class Doctrine_Sequence_Jdbcbridge extends Doctrine_Sequence
{
    public function nextId($seqName, $ondemand = true)
    {
        $sequenceName = $this->conn->quoteIdentifier($this->conn->formatter->getSequenceName($seqName), true);
        $query = 'SELECT NEXTVAL FOR ' . $sequenceName . ' AS VAL FROM SYSIBM.SYSDUMMY1';
        
        try {
            $result = $this->conn->fetchOne($query);
            $result = ($result) ? $result['VAL'] : null; 
        } catch(Doctrine_Connection_Exception $e) {
            if ($onDemand && $e->getPortableCode() == Doctrine_Core::ERR_NOSUCHTABLE) {
                try {
                    $result = $this->conn->export->createSequence($seqName);
                } catch(Doctrine_Exception $e) {
                    throw new Doctrine_Sequence_Exception('on demand sequence ' . $seqName . ' could not be created');
                }
                
                return $this->nextId($seqName, false);
            } else {
                throw new Doctrine_Sequence_Exception('sequence ' .$seqName . ' does not exist');
            }
        }
        return $result;
    }
    
    public function currId($sequenceName)
    {
        $sql = 'SELECT PREVVAL FOR '
             . $this->conn->quoteIdentifier($this->conn->formatter->getSequenceName($sequenceName))
             . ' AS VAL FROM SYSIBM.SYSDUMMY1';

        $stmt   = $this->conn->getDbh()->query($sql);
        $result = $stmt->fetchAll(Doctrine_Core::FETCH_ASSOC);
        if ($result) {
            return $result[0]['VAL'];
        } else {
            return null;
        }
    }

    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        $sql = 'SELECT IDENTITY_VAL_LOCAL() AS VAL FROM SYSIBM.SYSDUMMY1'; // FIXME
        $stmt = $this->conn->getDbh()->query($sql);
        $result = $stmt->fetchAll(Doctrine_Core::FETCH_ASSOC);
        if ($result) {
            return $result[0]['VAL'];
        } else {
            return null;
        }
    }
}