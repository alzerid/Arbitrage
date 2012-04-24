<?
namespace Arbitrage2\DB2;

class CMongoModelQuery extends CModelQuery
{
	public function findOne($query)
	{
		$this->_cmd   = 'findOne';
		$this->_query = $query;

		return $this;
	}

	public function findAll($query)
	{
		$this->_cmd   = 'find';
		$this->_query = $query;

		return $this;
	}

	public function execute()
	{
		//TODO: DB Factory
		
		//Execute command
		$class = $this->_class;
		$prop  = $class::properties();

		//Query
		$handle = new \Mongo;
		$handle = $handle->{$prop['database']}->{$prop['table']};
		$res    = $handle->{$this->_cmd}($this->_query);

		if($res === NULL)
			return NULL;

		//Check if multiple entries, if so return list
		if($this->_cmd === "find")
		{
			//Limit
			if($this->_limit !== NULL)
				$res = $res->limit($this->_limit);

			//Create ModelList
			if($res->count() > 0)
			{
				$list = new CMongoModelResults($res, $this->_class);
				return $list;
			}
		}
		elseif($this->_cmd === "findOne")
		{
			$class = $this->_class;
			return $class::model($res);
		}

		die("unknown execution");
	}
}
?>
