<?
namespace Framework\Database\DataTypes;

class CDatabaseIDDataType implements \Framework\Interfaces\IModelDataType
{
	protected $_id;

	/**
	 * Instantiate method creates a new ID
	 * @param $id The id to instantiate to.
	 */
	public function __construct($id=NULL)
	{
		if($id instanceof \Framework\Database\DataTypes\CDatabaseIDDataType)
			$this->_id = $id->_id;
		else
			$this->setValue($id);
	}

	/**
	 * Method sets the ID.
	 * @param $id The id to set to.
	 */
	public function setValue($id=-1)
	{
		if($id instanceof \Framework\Database\DataTypes\CDatabaseIDDataType)
			$this->_id = $id->_id;
		else
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

	/**
	 * Method that converts to string.
	 */
	public function __toString()
	{
		return $this->_id;
	}
}
?>
