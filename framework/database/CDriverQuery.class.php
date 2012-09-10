<?
namespace Framework\Database;

abstract class CDriverQuery
{
	protected $_class;    //Class to use when creating Models
	protected $_driver;   //Driver object to use for querying

	//Query
	protected $_query;
	protected $_data;
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
		$this->_class  = $class;
		$this->_driver = $driver;

		//Query
		$this->_query = NULL;
		$this->_data  = NULL;
		$this->_cmd   = NULL;
	}

	abstract public function findOne($query);
	abstract public function findAll($query);
	abstract public function count($query);
	abstract public function update($query, $data);
	abstract public function upsert($query, $data);
	abstract public function insert($data);
	abstract public function save($data);
	abstract public function remove($query);

	//Actually execute
	/**
	 * Abstract method that must be defined in the driver.
	 * @param \Framework\Database\CDatabaseModelCollection $results The result object to use for querying and setting the results.
	 * @return \Framework\Database\CDatabaseModelCollection Retuns the result.
	 */
	abstract public function execute(\Framework\Database\CDatabaseModelCollection $results);

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
}
?>
