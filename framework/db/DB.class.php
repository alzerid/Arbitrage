<?
class DB
{
	protected $db;
	protected $database;
	protected $host;
	protected $user;
	protected $pass;

	function __construct($database, $host, $user, $pass)
	{
		$this->database = $database;
		$this->host     = $host;
		$this->user     = $user;
		$this->pass     = $pass;
		$this->db       = NULL;
	}

	public function doesTableExist($table)
	{
		$db = $this->openDB();

		//$res = mysql_list_tables($this->database, $db);
		$res = mysqli_query($db, "SHOW TABLES FROM `${$this->database}`") or trigger_error(mysqli_error($this->db));
		while($row = mysqli_fetch_row($res))
		{
			if($row[0] == $table)
				return true;
		}

		return false;
	}

	protected function openDB()
	{
		if($this->db === NULL)
		{
			$this->db = mysqli_connect($this->host, $this->user, $this->pass, $this->database) or trigger_error(mysqli_error($this->db));
			//mysqli_select_db($this->db) or trigger_error(mysqli_error($this->db));
		}

		return $this->db;
	}

	public function query($query, $buffered=true)
	{
		$this->openDB();
		$res = mysqli_query($this->db, $query, $buffered ? MYSQLI_STORE_RESULT : MYSQLI_USE_RESULT) or trigger_error(mysqli_error($this->db));

		return $res;
	}

	public function getInsertID()
	{
		return mysqli_insert_id($this->db);
	}

	public function getAffectedRows()
	{
		return mysqli_affected_rows($this->db);
	}

	public function fetchRow($row)
	{
		$ret = mysqli_fetch_row($row);
		return $ret;
	}

	public function fetchAssoc($row, $type=MYSQL_ASSOC)
	{
		$ret = mysqli_fetch_array($row, $type);
		return $ret;
	}

	public function fetchObject($row)
	{
		$ret = mysqli_fetch_object($row);
		return $ret;
	}

	public function fetchCompleteRow($res)
	{
		$ret = array();
		while(($assoc = $this->fetchRow($res)))
			$ret[] = $assoc;

		return $ret;
	}

	public function fetchCompleteAssoc($res)
	{
		$ret = array();
		while(($assoc = $this->fetchAssoc($res)))
			$ret[] = $assoc;

		return $ret;
	}

	public function fetchCompleteObject($res)
	{
		$ret = array();
		while(($assoc = $this->fetchObject($res)))
			$ret[] = $assoc;

		return ((count($ret) == 0)? false : $ret);
	}

	static public function emptyResult($res)
	{
		return ($res === NULL || $res === false || count($res) == 0);
	}

	static public function buildQuery($type, $table, $id, $entries)
	{
		$query = "";
		switch($type)
		{
			case "update":
				$query = "UPDATE $table SET ";

				foreach($entries as $k => $v)
				{
					if(is_null($v))
						$query .= "`$k`=NULL, ";
					elseif($k != $id)
						$query .= "`$k`=\"$v\", ";
				}

				$query = substr($query, 0, strlen($query)-2);
				if($id)
				{
					if(substr($id, 0, 2) == "l:")
					{
						$val    = substr($id, 2);
						$query .= " WHERE $id LIKE \"$val\"";
					}
					else
						$query .= " WHERE $id={$entries[$id]}";
				}

				break;

			case "replace":
				$query = "REPLACE $table SET ";

				foreach($entries as $k => $v)
				{
					if(is_null($v))
						$query .= "$k=NULL, ";
					else
						$query .= "$k=\"$v\", ";
				}

				$query = substr($query, 0, strlen($query)-2);

				break;

			case "insert":
				$query = "INSERT INTO $table ";

				$cols = "";
				$vals = "";
				foreach($entries as $k => $v)
				{
					$cols .= "$k, ";

					if(is_null($v))
						$vals .= "NULL, ";
					else
						$vals .= "'$v', ";
				}

				$cols = substr($cols, 0, strlen($cols)-2);
				$vals = substr($vals, 0, strlen($vals)-2);

				$query .= "($cols) VALUES($vals);";

				break;

			case "select":
				$query   = "SELECT * FROM $table";
				$limitby = '';
				$orderby = '';
				$groupby = '';
				$where   = '';

				if(count($entries)>0)
				{
					foreach($entries as $k=>$v)
					{
						if(strpos($k, "l:") === 0)
							$where .= substr($k, 2) . " LIKE '$v' AND ";
						elseif(strpos($k, "i:") === 0 || strpos($k, "!i:") === 0)
						{
							$vals   = implode(",", $v);

							//Check if the first char is a ! if so we do a negate
							if($k[0] == "!")
							{
								$in  = "NOT IN";
								$pos = 3;
							}
							else
							{
								$in  = "IN";
								$pos = 2;
							}

							$where .= substr($k, $pos) . " $in ($vals) AND ";
						}
						elseif(strpos($k, "b:") === 0)
							$where .= "$k BETWEEN '{$v[0]}' AND '{$v[1]}' AND ";
						elseif(strpos($k, "c:") === 0)  //Compare to table object
						{
							$spos = strpos($v, ':');
							$cmp  = substr($v, 0, $spos);
							$val  = substr($v, $spos+1);

							$spos = strpos($k, ":")+1;
							$k    = substr($k, $spos);

							$where .= "$k $cmp $val AND ";
						}
						elseif(strpos($k, '_o:') === 0)
							$orderby = "ORDER BY " . substr($k, 3) . " " . $v;
						elseif(strpos($k, "_l:") === 0)
							$limitby = "LIMIT " . $v;
						elseif(strpos($k, "_g:") === 0)
							$groupby = "GROUP BY $v";
						else
							$where .= "$k='$v' AND ";
					}

					if(strlen($where) > 0)
					{
						$where = "WHERE $where";
						$where = substr($where, 0, strlen($where)-4);
					}

					$query = "$query $where $groupby $orderby $limitby";
					$query = trim($query);
				}

				break;
		}

		return $query;
	}
}
?>
