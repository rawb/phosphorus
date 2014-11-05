<?php
class Phosphorus_Core_DatabaseObject
{
    protected $_fields = null;
    protected $_tableName = null;
    protected $_dbHandler = null;

    public function __construct($meta)
    {
        $tableName = explode("_",get_class($this));
        $this->_tableName  = strtolower($tableName[1]);
        $this->_dbHandler  = $meta["db_handler"];
    }

    public function insert($keyValuePairs)
    {
        $columns = "";
        $placeholders = "";
        foreach($keyValuePairs as $column => $value){
            $columns .= $column;
            $placeholders .= ':'.$column;
            if(end($keyValuePairs) !== $value){
                $placeholders .= ",";
                $columns .= ",";
            }
        }

        $sql = 'INSERT INTO '.$this->_tableName.' ('.$columns.') VALUES ('.$placeholders.')';
        $query = $this->_dbHandler->prepare($sql);

        $result = $query->execute($keyValuePairs);
        if(!$result){
            throw new Exception('Insert failure.');
        }
    }

    public function update($id,$keyValuePairs)
    {
        $columns = "";
        $values = array();
        foreach($keyValuePairs as $column => $value){
            $values[] = $value;
            $columns .= $column." = ?";
            if(end($keyValuePairs) !== $value){
                $columns .= ",";
            }
        }
        $values[] = $id;
        $sql = 'UPDATE '.$this->_tableName.' SET '.$columns.' WHERE id = ?';
        $query = $this->_dbHandler->prepare($sql);

        $result = $query->execute($values);
        if(!$result){
            throw new Exception('UPDATE failure.');
        }
    }

    public function delete($id)
    {
        $count = $this->_dbHandler->exec('DELETE FROM '.$this->_tableName.' WHERE id = '.'"'.$id.'"');
        if(count($count) == 0){
            throw new Exception('DELETE failure.');
        }
    }

    public function find($id)
    {
        $fetcher = $this->_dbHandler->query('SELECT * FROM '.$this->_tableName.' WHERE id = '.'"'.$id.'"');
        $fetcher->setFetchMode(PDO::FETCH_ASSOC);
        $result = $fetcher->fetch();
        if(isset($result)){
            return $result;
        }else{
            return false;
        }
    }

    public function fetchAll($whereFilters,$offset = null,$limit = null)
    {
        $formatted = "";
        if(count($whereFilters) < 0){
            $formatted .= "WHERE ";
            $values = array();
            foreach($whereFilters as $columnString => $value){
                $formatted .= $columnString;
                if(end($whereFilters) !== $value){
                    $formatted .= " AND ";
                }
                $values[] = $value;
            }
        }
        $offsetAndNull = "";
        if(limit != null){
            $offsetAndNull .= " LIMIT ".$limit;
        }
        if($offset != null){
            $offsetAndNull .= " OFFSET ".$offset;
        }

        $sql = 'SELECT * FROM '.$this->_tableName.' '.$formatted.' '.$offsetAndNull;

        $sth = $this->_dbHandler->prepare($sql);
        $sth->execute($values);
        $results = $sth->fetchAll();
        return $results;
    }
}
