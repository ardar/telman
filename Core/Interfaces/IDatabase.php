<?php
interface IDatabase
{
	public function __construct($dbms, $dbhost, $dbname, $dbuser, $dbpass);
	
	public function Init($options);
	
	public function Query($sql);
	
	public function TransBegin();
	
	public function TransCommit();
	
	public function TransRollback();
	
	public function FetchAll($sql);
	
	public function FetchRow($sql);
	
	public function FetchObject($sql);
	
	public function InsertId();
	
	public function Insert( $table_name, array $fields);

	public function Update($table_name, array $fields, $subquery);
	
	public function Select($table_name, $subquery);
	
	public function Delete($table_name, $subquery);
	
	public function DeleteSingle($table, $pk, $pkvalue);
	
	public function DeleteList($table, $pk, array $pkvalues);
	
	public function GetQueryTimes();
}