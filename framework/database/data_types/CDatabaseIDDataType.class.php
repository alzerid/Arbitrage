<?
namespace Framework\Database\DataTypes;

class CDatabaseIDDataType implements \Framework\Interfaces\IModelDataType
{
	protected $_id;

	public function __construct()
	{
		$this->_id = NULL;
	}

	/**
	 * Instantiate method creates a new ID
	 * @param $id The id to instantiate.
	 * @return \Framework\Database\DataTypes\CDatabaseIDDataType
	 */
	static public function instantiate($id)
	{
		$class    = get_called_class();
		$obj      = new $class;

		$obj->setValue($id);

		return $obj;
	}

	/**
	 * Method sets the ID.
	 * @param $id The id to set to.
	 */
	public function setValue($id=-1)
	{
		$this->_id = $id;
	}

	/**
	 * Method returns the ID.
	 * @return Returns the id.
	 */
	public function getValue()
	{
		return $this->_id;
	}
}
?>
