<?php

class Doctrine_Import_Splice extends Doctrine_Import
{
    protected $sql  = array(
                            'listDatabases'   => 'SHOW DATABASES',
                            'listTableFields' => 'DESCRIBE %s',
                            'listSequences'   => 'SHOW TABLES',
                            'listTables'      => 'SELECT TABLENAME FROM SYS.SYSTABLES',
                            'listUsers'       => 'SELECT DISTINCT USER FROM USER',
                            'listViews'       => "SHOW FULL TABLES %s WHERE Table_type = 'VIEW'",
                            );

    public function listTables($database = null)
    {
        return $this->conn->fetchColumn($this->sql['listTables']);
    }
}
