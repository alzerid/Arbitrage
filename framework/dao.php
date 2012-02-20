<?
define("DAO_RETURN_OBJECT", 0);
define("DAO_RETURN_ASSOC", 1);
define("DAO_RETURN_ARRAY", 2);

class DAO
{
	static private $_instances = array();

	private $_vos;
	private $_joins;
	private $_limit;
	private $_dbconf;

  //Cache types
  private $_read_cache;
  private $_write_cache;

	//Return type
	private $_return_type;

	public function __construct($name, $read_cache=false, $write_cache=false)
	{
		$this->reset();
		$this->_dbconf      = $name;
    $this->_read_cache  = NULL;
    $this->_write_cache = NULL;

    //Setup caching
    if($read_cache)
      $this->_read_cache = CacheFactory::getCache('memcache');

    if($write_cache)
      $this->_write_cache = CacheFactory::getCache('redis');

		$this->setDatabase($name);
		$this->_return_type = DAO_RETURN_OBJECT;
	}

	static public function getInstance($db, $read_cache, $write_cache)
	{
		$key = "{$db}_{$read_cache}_{$write_cache}";
		if(array_key_exists($key, DAO::$_instances))
			return DAO::$_instances[$key];

		$ret = new DAO($db, $read_cache, $write_cache);
		DAO::$_instances[$key] = $ret;

		return $ret;
	}

	public function reset()
	{
		$this->_vos         = array();
		$this->_joins       = array();
		$this->_limit       = NULL;
	}

	private function getWhereString($vo)
	{
		$filter = $vo['_filters'];
		if(count($filter) == 0)
			return "";

		$ret = "";
		foreach($filter as $k=>$v)
			$ret = " {$vo['_alias']}.$k='$v' AND ";

		return $ret;
	}

	private function getGroupByString($vo)
	{
		if($vo['_group'] === NULL)
			return "";

		$groupby = $vo['_group'];
		$ret     = "";

		if(is_array($groupby))
		{
			foreach($groupby as $v)
				$ret = " {$vo['_alias']}.$v, ";
		}
		else
			$ret = " {$vo['_alias']}.$groupby, ";

		return $ret;
	}

	public function getOrderByString($vo)
	{
		if($vo['_order'] === NULL)
			return "";

		$orderby = $vo['_order'];
		$ret     = "";

		if(!is_array($orderby))
			Error::throwError('dao', __FILE__, 'order by must be an array');

		foreach($orderby as $k=>$v)
			$ret .= " {$vo['_alias']}.$k $v, ";

		return $ret;
	}

	public static function includeVO($filename)
	{
		global $_conf;

		$path = "{$_conf['fsrootpath']}framework/lib/dao/vos/{$filename}VO.php";
		if(!file_exists($path))
      FastLog::logit("dao", __FILE__, "Unable to find value object class '$filename'.");

		require_once($path);
	}

	public static function getModel($filename)
	{
		global $_conf;

		$path = "{$_conf['fsrootpath']}framework/lib/dao/models/{$filename}Model.php";
		if(!file_exists($path))
      FastLog::logit("dao", __FILE__, "Unable to find value object class '$filename'.");

		require_once($path);

		$class = "{$filename}Model";
		return new $class;
	}

	public function setReturnType($type)
	{
		$this->_return_type = $type;
	}

  public function setDatabase($db)
  {
    //TODO: Make sure config is configured
    global $_conf;

    $_dbconf = $_conf['db'][$db];
  }

	public function setLimit($limit)
	{
		$this->_limit=$limit;
	}

	/** Binds this DAO with a VO */
	public function bind($vo)
	{
		//TODO: Check if VO already exists in array

		$vo   .= "VO";
		$item  = new $vo;

		$this->_vos[$vo] = array("_database" => $item->_database,
		                         "_table"    => $item->_table,
														 "_entries"  => "*",
														 "_filters"  => NULL,
														 "_afilters" => NULL,
														 "_alias"    => "t" . (count($this->_vos) + 1),
														 "_order"    => NULL,
														 "_group"    => NULL); 
	}

	public function unbind($vo)
	{
		//TODO: Check if VO already exists in array

		unset($this->_vos[$vo . "VO"]);
	}

	public function setGroup($vo, $column)
	{
		//TODO: Check if vo exists in DAO
		//TODO: Check if column exists in VO

		$this->_vos[$vo . "VO"]['_group'] = $column;
	}

	public function appendJoin($vo1, $vo2, $join1, $join2)
	{
		//TODO: Check if vo exists in DAO


		if($vo1 != NULL)
			$vo1 = &$this->_vos[$vo1 . "VO"];

		$vo2 = &$this->_vos[$vo2 . "VO"];

		//Setup joins
		$this->_joins[] = array('vo1' => &$vo1, 'vo2' => &$vo2, 'join1' => $join1, 'join2' => $join2);
	}

	public function clearJoin()
	{
		$this->_joins = array();
	}

	public function setEntries($vo, $entries=NULL)
	{
		//TODO: Check if vo exists in DAO



		if($entries)
			$this->_vos[$vo . "VO"]['_entries'] = $entries;
		else
		{
			$this->_vos[$vo . "VO"]['_entries'] = "*";
		}
	}

	public function setOrder($vo, $order)
	{
		//TODO: Make sure we have the vos

		$vo = &$this->_vos[$vo . "VO"];
		$vo['_order'] = $order;
	}

	public function setFilter($vo, $filters=NULL)
	{
		//TODO: Make sure we have vos


		$vo = &$this->_vos[$vo . "VO"];
		$vo['_filters'] = $filters;
	}

	public function setAdvancedFilter($vo, $filter)
	{
		//TODO: Make sure we have vos


		$vo = &$this->_vos[$vo . "VO"];
		$vo['_afilters'] = $filter;
	}

	//TODO: Code the set from method
	public function setFrom($vo)
	{
	}

	public function populateGetQuery($alimit=NULL)
	{
		//Select Clause
		$query = "SELECT ";
		$where = "";
		$group = "";
		$order = "";
		foreach($this->_vos as $vo)
		{
			$select = "";
			if(is_array($vo['_entries']))
			{
				foreach($vo['_entries'] as $entry)
					$select .= " {$vo['_alias']}.{$entry}, ";
			}
			else
				$select = " {$vo['_alias']}.{$vo['_entries']}, ";

			//Where clause
			if($vo['_afilters'] !== NULL)
				$where .= " " . $vo['_afilters'] . "  AND";
			else
				$where .= " " . $this->getWhereString($vo) . " ";

			//Order/Group By clause
			$group .= " " . $this->getGroupByString($vo);
			$order .= " " . $this->getOrderByString($vo);
			$query .= $select;
		}

		//Normalize with no commas
		$query = substr($query, 0, -2) . " ";

		//From clause
		reset($this->_vos);
		$key  = key($this->_vos);
		reset($this->_vos);
		$vo = $this->_vos[$key];

		$fromAlias = $vo['_alias'];
		$from      = " FROM {$vo['_database']}.{$vo['_table']} {$vo['_alias']} ";
		$query    .= $from;

		//Join statements
		$join = "";
		foreach($this->_joins as $j)
		{
			$join .= " JOIN {$j['vo2']['_database']}.{$j['vo2']['_table']} {$j['vo2']['_alias']} ON {$j['vo2']['_alias']}.{$j['join2']} = ";
			if($j['vo1'] == NULL)
			{
				//From alias
				$join .= "$fromAlias.{$j['join1']} ";
			}
			else
				$join .= "{$j['vo1']['_alias']}.{$j['join1']} ";
		}

		//Normalize where clause
		$where = trim($where);
		if($where !== "")
			$where = "WHERE " . substr($where, 0, -3);

		//Normalize group by clause
		$group = trim($group);
		if($group !== "")
			$group = "GROUP BY " . substr($group, 0, -1);

		//Normalize the order by clause
		$order = trim($order);
		if($order !== "")
			$order = "ORDER BY " . substr($order, 0, -1);

		//Limit clause
		$limit = "";
		if($alimit !== NULL)
			$limit = " LIMIT $alimit ";
		else if($this->_limit !== NULL)
			$limit = " LIMIT {$this->_limit} ";

		$query = "$query $join $where $group $order $limit";
		$query = trim($query);

		return $query;
	}

	//Just do a query via string
	public function query($query)
	{
		global $_conf;

		//Check to see if we have it in read cache
		if($this->_read_cache)
		{
			$key = "dao_{$this->_return_type}_" . md5($query);
			$ret = $this->_read_cache->get($key);

			if($ret !== false)
				return $ret;
		}

		//Query
		$db    = DBFactory::getDB($this->_dbconf, $_conf['db_driver']);
		$res   = $db->query($query);
		
		if($res == NULL || $res->num_rows == 0)
			return NULL;

		//Figure out return type
		switch($this->_return_type)
		{
			case DAO_RETURN_ARRAY:
				$ret = $db->fetchCompleteArray($res);
				break;

			case DAO_RETURN_ASSOC:
				$ret = $db->fetchCompleteAssoc($res);
				break;

			case DAO_RETURN_OBJECT:
			default:
				$ret = $db->fetchCompleteObject($res);
				break;
		}

		//If cache then save it
		if($this->_read_cache)
		{
			$key = "dao_{$this->_return_type}_" . md5($query);
			$this->_read_cache->set($key, $ret, NULL, 300);
		}

		return $ret;

	}

	public function get($alimit = NULL)
	{
    global $_conf;

		//TODO: Make sure we have VOs


		//Build the query
		$query = $this->populateGetQuery($alimit);
		return $this->query($query);
	}

  public function insert($vo, $immediate_write=false)
  {
    $excludes = array('id', '_table', '_database');

    //TODO: Check if the paramater passed is a VO
    $query = "INSERT INTO {$vo->_table} ";
    $vars  = get_object_vars($vo);

    //Remove the excluded variables from $vars
    foreach($excludes as $ex)
    {
      if(array_key_exists($ex, $vars))
        unset($vars[$ex]);
    }

    //Set columns and values
    $columns = "";
    $values  = "";

    foreach($vars as $k=> $v)
    {
      $columns .= "$k,";
      $values  .= "'{$vo->$k}',";
    }

    //Normalize the strings
    $columns = substr($columns, 0, strlen($columns)-1);
    $values  = substr($values, 0, strlen($values)-1);

    $query .= " ($columns) VALUES($values);";



    //Push the query into write cache for delayed inserting
    $ret = false;
    if($this->_write_cache && !$immediate_write)
      $ret = $this->_write_cache->lPush("delayed_{$vo->_database}", $query);
    else  //Do the actual DB query
		{
			global $_conf;

			//Query
			$db  = DBFactory::getDB($this->_dbconf, $_conf['db_driver']);
      $ret = $db->query($query);
		}
      
    return $ret;
  }
}
?>
