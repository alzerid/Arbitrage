<?
namespace Arbitrage2\Model2;

abstract class CModelQuery
{
	protected $_class;

	//Query
	protected $_query;
	protected $_data;
	protected $_limit;
	protected $_skip;
	protected $_cmd;
	protected $_sort;

	public function __construct($class)
	{
		//Database
		$this->_class  = $class;

		//Query
		$this->_query = NULL;
		$this->_data  = NULL;
		$this->_limit = NULL;
		$this->_skip  = NULL;
		$this->_cmd   = NULL;
		$this->_sort  = NULL;
	}

	abstract public function findOne($query);
	abstract public function findAll($query);
	abstract public function update($query, $data);
	abstract public function save($query, $data);

	//Actually execute
	abstract public function execute();

	//Other options
	public function sort($sort)
	{
		$this->_sort = $sort;
		return $this;
	}

	public function limit($limit)
	{
		$this->_limit = $limit;
		return $this;
	}

	public function skip($skip)
	{
		$this->_skip = $skip;
		return $this;
	}
}
?>
