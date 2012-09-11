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
	abstract public function getQueryDriver($class);

	/**
	 * Abstract method reutns a batch object.
	 */
	abstract public function getBatchDriver();

	/**
	 * Method converts native data types to model data types.
	 * @param $data The data to convert.
	 * @return Returns the the Model Data Type.
	 */
	abstract public function convertNativeDataTypeToModelDataType($data);

	/**
	 * Method converts model data types to native data types.
	 * @param $data The data to convert.
	 * @return Returns the the Native Data Type.
	 */
	abstract public function convertModelDataTypeToNativeDataType($data);

	/**
	 * Method converts model structure to native structure.
	 * @param $data The data to convert.
	 * @return Returns the the Model Structure.
	 */
	abstract public function convertModelStructureToNativeStructure($data);

	/**
	 * Method converts native structure to model structure.
	 * @param $data The data to convert.
	 * @return Returns the the Native Structure.
	 */
	abstract public function convertNativeStructureToModelStructure($data);

	/**
	 * Method converts the native ID data type to a model id data type.
	 * @param $id The id to convert.
	 * @param \Framework\Database\DataTypte\CDatabaseIDDataType Returns the Model ID data type.
	 */
	abstract public function convertNativeIDtoModelID($id);

	/**
	 * Method converts the native ID data type to a model id data type.
	 * @param \Framework\Database\DataTypes\CDatabaseIDDataType $id The id to convert.
	 * @param \MongoId Returns the Model ID data type.
	 */
	abstract public function convertModelIDtoNativeID(\Framework\Database\DataTypes\CDatabaseIDDataType $id);
}
?>
