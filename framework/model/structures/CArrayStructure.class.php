<?
namespace Framework\Model\Structures;

//TODO: Add iterator implementation
class CArrayStructure extends \Framework\Model\CModel implements \Iterator
{
	protected $_class;
	protected $_idx;

	/**
	 * Method instantiates the data type.
	 * @param $data The variables to set as default data for this Model.
	 * @param $class The class associated with the values.
	 */
	public function __construct($data=array(), $class=NULL)
	{
		$this->_idx   = -1;
		$this->_class = $class;

		//Set data
		if(is_array($data))
			$this->_data = $data;
		elseif($data instanceof \Framework\Model\Structures\CArrayStructure)
			$this->_data = $data->_data;
		else
			throw new \Framework\Exceptions\EModelStructureException("Unable to handle data conversion.");
	}

	/**
	 * Method finds the first value encountered from $offset.
	 * @param $search The data to search for within the array.
	 * @return Returns the index.
	 */
	public function search($search)
	{
		return array_search($search, $this->_data);
	}

	/*****************************/
	/** Iterator Implementation **/
	/*****************************/

	public function current()
	{
		return $this->_data[$this->_idx];
	}

	public function key()
	{
		return $this->_idx;
	}

	public function next()
	{
		$this->_idx++;
	}

	public function rewind()
	{
		$this->_idx=0;
	}

	public function valid()
	{
		return array_key_exists($this->_idx, $this->_data);
	}

	/*********************************/
	/** END Iterator Implementation **/
	/*********************************/

	/****************************/
	/** CObjectAccess Overload **/
	/****************************/

	/**
	 * Method sets the data associated with this model.
	 * @param $name The attribute name to set.
	 * @param $val The value to set the attribute.
	 */
	protected function _setData($name, $val)
	{
		if($name === "")
			$this->_data[] = $val;
		elseif(is_numeric($name))
		{
			$idx = (int) $name;
			$this->_data[$idx] = $val;
		}
		else
		{
			var_dump($val);
			throw new \Framework\Exceptions\EModelStructureException("Invalid set data index.");
		}
	}

	/**
	 * Method retrieves the data associated with this model.
	 * @param $name The attribute name to retrieve.
	 */
	protected function _getData($name)
	{
		//TODO: Check __unset
		if(!is_numeric($name))
			throw new \Framework\Exceptions\EModelStructureException("Index must be numeric.");

		$idx = (int) $name;
		if(array_key_exists($idx, $this->_data))
			return $this->_data[$idx];

		return NULL;
	}

	/**
	 * Method removes the attribute from the model.
	 * @param $idx The index of the attribute to remove.
	 */
	protected function _unsetData($idx)
	{
		$idx = (int) $idx;
		array_splice($this->_data, $idx, 1);
	}

	/**
	 * Method determines if there is an attribute set.
	 * @param $idx The attribute to check if it is set or not.
	 * @return Returns true if set else false.
	 */
	protected function _issetData($idx)
	{
		$idx = (int) $idx;

		die(__METHOD__);

		return array_key_exists($name, $this->_data);
	}
	/********************************/
	/** END CObjectAccess Overload **/
	/********************************/

}
?>
