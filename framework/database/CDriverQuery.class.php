<?
namespace Framework\Database;

abstract class CDriverQuery implements \ArrayAccess, \Iterator
{
	protected $_class;        //Class to use when creating Models
	protected $_driver;       //Driver object to use for querying
	protected $_collection;   //Collection set

	//Query
	protected $_query;
	protected $_data;
	protected $_sort;
	protected $_limit;
	protected $_skip;
	protected $_cmd;

	/**
	 * Method initializes the CModelQuery
	 * @param \Framework\Database\CDatabaseDriver $driver The database driver to associate with.
	 * @param string $class The string representation of the model to associate with.
	 */
	public function __construct(\Framework\Database\CDatabaseDriver $driver, $class)
	{
		//Database
		$this->_class      = $class;
		$this->_driver     = $driver;
		$this->_collection = NULL;

		//Query Modifiers
		$this->_sort  = NULL;
		$this->_limit = NULL;
		$this->_skip  = NULL;

		//Query
		$this->_query = NULL;
		$this->_data  = NULL;
		$this->_cmd   = NULL;
	}

	abstract public function findOne($query);
	abstract public function findAll($query);
	//abstract public function count($query);
	abstract public function update($query, $data);
	abstract public function upsert($query, $data);
	abstract public function insert($data);
	abstract public function save($data);
	abstract public function remove($query);

	/**
	 * Abstract method that must be defined in the driver.
	 * @return \Framework\Database\CDatabaseModelCollection Retuns the result.
	 */
	abstract public function execute();

	/**
	 * Method returns the model class associated with this query object.
	 * @return string Returns the class.
	 */
	public function getClass()
	{
		return $this->_class;
	}

	/**
	 * Method returns the associated driver to the query object.
	 * @return \Framework\Database\CDatabaseDriver Returns the database driver.
	 */
	public function getDriver()
	{
		return $this->_driver;
	}

	/**
	 * Method returns the collection associated with the query.
	 * @return \Framework\Database\CDatabaseModelCollection Returns the collection.
	 */
	public function getCollection()
	{
		return $this->_collection;
	}

	/** Query Property Modifiers **/

	/**
	 * Methods sets a sort on the query.
	 * @param $sort The sorting property to set when querying.
	 * @returns \Framework\Database\CDatabaseModelCollection Returns itself.
	 */
	public function sort($sort)
	{
		$this->_sort = $sort;
		return $this;
	}


	/**
	 * Methods sets a limit on the query.
	 * @param $limit The limit property to set when querying.
	 * @returns \Framework\Database\CDatabaseModelCollection Returns itself.
	 */
	public function limit($limit)
	{
		$this->_limit = $limit;
		return $this;
	}

	/**
	 * Methods sets a skip on the query.
	 * @param $skip The skip property to set when querying.
	 * @returns \Framework\Database\CDatabaseModelCollection Returns itself.
	 */
	public function skip($skip)
	{
		$this->_skip = $skip;
		return $this;
	}
	/** END Query Property Modifiers **/

	/*****************************/
	/** Iterator Implementation **/
	/*****************************/

	public function count()
	{
		return $this->_getCollection()->count();
	}

	public function current()
	{
		return $this->_getCollection()->current();
	}

	public function key()
	{
		return $this->_getCollection()->key();
	}

	public function next()
	{
		return $this->_getCollection()->next();
	}

	public function rewind()
	{
		return $this->_getCollection()->rewind();
	}

	public function valid()
	{
		return $this->_getCollection()->valid();
	}

	/*********************************/
	/** END Iterator Implementation **/
	/*********************************/

	/**
	 * Method gets the collection and assigns it locally.
	 */
	abstract protected function _getCollection();
}
?>
