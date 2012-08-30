<?
namespace Framework\Database;
abstract class CDatabaseDriver implements \Framework\Interfaces\IDatabaseDriver
{
	protected $_handle;
	protected $_config;
	protected $_database;
	protected $_table;

	/**
	 * Class holds the handle and other information for the driver connection.
	 * @param $config The config to use for connection purposes.
	 */
	public function __construct($config)
	{
		$this->_handle   = NULL;
		$this->_config   = $config;
		$this->_database = NULL;
		$this->_table    = NULL;
	}

	/**
	 * Method returns the raw handle of the driver.
	 * @return Returns the handle.
	 */
	public function getHandle()
	{
		return $this->_handle;
	}

	/**
	 * Method retuns the configuration of this driver.
	 * @returns array Returns driver configuration.
	 */
	public function getConfig()
	{
		if($this->_database !== NULL)
			$this->_config['database'] = $this->_database;

		if($this->_table !== NULL)
			$this->_config['table'] = $this->_table;

		return $this->_config;
	}

	/** Start IDatabaseDriver Implementation **/

	/**
	 * Method sets the database.
	 * @param $database The database to set the driver to.
	 */
	public function setDatabase($database)
	{
		$this->_database = $database;
	}

	/**
	 * Method sets the table.
	 * @param $table The table to set the driver to.
	 */
	public function setTable($table)
	{
		$this->_table = $table;
	}

	/** End IDatabaseDriver Implementation **/


	/**
	 * Abstract method returns the correct Query class.
	 */
	abstract public function getQuery($class);

	/**
	 * Abstract method reutns a batch object.
	 */
	abstract public function getBatch();
}
?>
