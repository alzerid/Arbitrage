<?
namespace Framework\Model;

class CMomentoModel extends \Framework\Model\CModel
{
	protected $_variables;

	public function __construct()
	{
		parent::__construct();
		$this->_variables = array();
	}

	/**
	 * Method merges _variables into _data.
	 */
	public function merge()
	{
		foreach($this->_variables as $key=>$val)
		{
			//TODO: Code Structure Type
			/*if($val instanceof \Framework\Model\CModel)
			{
				die("\\Framework\\Model\\CMomentoModel::merge -- \\Framework\\Model\\CModel");
			}*/

			$this->_data[$key] = $val;
		}

		$this->clear();
	}

	/**
	 * Method clears any updated variables.
	 */
	public function clear()
	{
		$this->_variables = array();
	}

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
		$this->_variables[$name] = $val;
	}

	/**
	 * Method retrieves the data associated with this model.
	 * @param $name The attribute name to retrieve.
	 */
	protected function _getData($name)
	{
		if(array_key_exists($name, $this->_variables))
			return $this->_variables[$name];

		return parent::_getData($name);
	}

	/**
	 * Method removes the attribute from the model.
	 * @param $name The name of the attribute to remove.
	 */
	protected function _unsetData($name)
	{
		unset($this->_variables[$name]);
		parent::_unsetData($name);
	}

	/**
	 * Method determines if there is an attribute set.
	 * @param $name The attribute to check if it is set or not.
	 * @return Returns true if set else false.
	 */
	protected function _issetData($name)
	{
		return array_key_exists($name, $this->_variables) || parent::_issetData($name);
	}

	/********************************/
	/** END CObjectAccess Overload **/
	/********************************/

}
?>
