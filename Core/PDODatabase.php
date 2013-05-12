<?php
class PDODatabase implements IDatabase
{
	/**
	 * PDO object instance.
	 * @var PDO
	 */
	private $_db = null;
	private $_dbms;
	private $_dbhost;
	private $_dbname;
	private $_dbuser;
	private $_dbpass;
	private $_querytimes = 0;
	
	private function _ChkConn()
	{
		if(!$this->_db)
		{
			throw new PCException("db not connected");
		}
		return true;
	}
	
	public function __construct($dbms, $dbhost, $dbname, $dbuser, $dbpass)
	{
		$this->_dbms = $dbms;
		$this->_dbhost = $dbhost;
		$this->_dbname = $dbname;
		$this->_dbuser = $dbuser;
		$this->_dbpass = $dbpass;
	}
	
	public function Init($options)
	{
		$dsn = $this->_dbms.":host=".$this->_dbhost.";dbname=".$this->_dbname;
		$this->_db = @new PDO($dsn, $this->_dbuser, $this->_dbpass, $options);
		
		$this->_db ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
		$this->_db ->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);  
		
		$this->_db->query("SET NAMES 'UTF8'");
	}
	
	/**
	 * query a sql 
	 */
	public function Query($sql)
	{
		$this->_ChkConn();
		$this->_querytimes ++;
		error_log($sql);
		return $this->_db->query($sql);
	}
	
	public function Exec($sql)
	{
		$this->_ChkConn();
		$this->_querytimes ++;
		error_log($sql);
		return $this->_db->exec($sql);
	}
	
	public function TransBegin()
	{
		$this->_ChkConn();
		return $this->_db->beginTransaction();
	}
	
	public function TransCommit()
	{
		$this->_ChkConn();
		return $this->_db->commit();
	}
	
	public function TransRollback()
	{
		$this->_ChkConn();
		return $this->_db->rollBack();
	}
	
	public function FetchAll($sql)
	{
		$this->_ChkConn();
		$statment = $this->Query($sql);
		if ($statment) 
		{
			return $statment->fetchAll();
		}
		return null;
	}
	
	public function FetchRow($sql)
	{
		$this->_ChkConn();
		$statment = $this->Query($sql);
		if ($statment) 
		{
			return $statment->fetch();
		}
		return null;
	}
	
	public function FetchObject($sql)
	{
		$this->_ChkConn();
		$statment = $this->Query($sql);
		if ($statment) 
		{
			return $statment->fetchObject();
		}
		return null;
	}
	
	public function InsertId()
	{
		$this->_ChkConn();
		return $this->_db->lastInsertId();
	}
	
	public function Insert($table_name, array $fields) 
	{
		$this->_ChkConn();
	    $query = '';
	
	    foreach ($fields as $index => $value) {
	
	        $value = addslashes($value);
	
	        if (!$query) {
	            $query = "`$index` = '$value'"; 
	        }
	        else {
	            $query .= ", `$index` = '$value'";
	        }
	
	    }
	
	   	$query = "INSERT INTO $table_name SET " . $query;
	    $link = $this->Exec($query);
	    $insertid = $this->InsertId();
	    if ($link && $insertid) 
	    {
	    	$link = $insertid;
	    }
	    return $link;
	}

	//update into a table name with given array where keys are table columns
	public function Update($table_name, array $fields, $subquery) 
	{
		$this->_ChkConn();
	    $query = '';
	
	    foreach ($fields as $index => $value) { 
	
	        $value = addslashes($value);
	
	        if (!$query) {
	            $query = "`$index` = '$value'"; 
	        }
	        else {
	            $query .= ", `$index` = '$value'";
	        }
	    }
	
	    $sql = "UPDATE $table_name SET " . $query . ' ' . $subquery;
	    return $this->Query($sql);
	}
	
	public function Select($table_name, $subquery)
	{
		$this->_ChkConn();
		$sql = "select * from $table_name $subquery";
		return $this->FetchAll($sql);
	}
	
	public function Delete($table_name, $subquery)
	{
		$this->_ChkConn();
		if (!$subquery) {
			return false;
		}
		$sql = "delete from $table_name $subquery";
	  	return $this->Query($sql);
	}
	
	public function DeleteSingle($table, $pk, $pkvalue)
	{
		$this->_ChkConn();
		$subsql = " where $pk ='$pkvalue'";
		return $this->Delete($table,$subsql);
	}
	
	public function DeleteList($table, $pk, array $pkvalues)
	{
		$this->_ChkConn();
		if (count($pkvalues)<=0) {
			return false;
		}
		foreach ($pkvalues as $value)
		{
			$list .= ($list ? ",$value" : $value);
		}
		if ($list=='') {
			return false;
		}
		$sql = "delete from $table where $pk in ($list)";
		return $this->Query($sql);
	}
	
	public function GetQueryTimes()
	{
		return $this->_querytimes;
	}
}