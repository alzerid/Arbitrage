<?
namespace Framework\Model;

class CModel extends \Framework\Utils\CObjectAccess
{
	protected $_data;

	public function __construct()
	{
		$this->_data = array();
	}

	/**
	 * Method overloaded from CObjectAccess used for creating and returning new models.
	 * @param $data The variables to set as default data for this Model.
	 */
	static public function instantiate($data=NULL)
	{
		$class = get_called_class();
		$obj   = new $class;
		$obj->_setModelData($data);

		return $obj;
	}

	/**************************/
	/** APath Implementation **/
	/**************************/
	
	/**
	 * Method traverses the values with Arbitrage Namespace Notation.
	 * @param string $path The arbitrage path.
	 * @param \Framework\Utils\CArrayObject $obj The object to traverse.
	 * @return mixed Returns a value or a \Framework\Utils\CArrayObject.
	 */
	public function apath($path, \Framework\Utils\CObjectAccess $obj=NULL)
	{
		//Start iterating
		if($obj===NULL)
			return $this->apath($path, $this);

		//Get key
		$path = explode('.', $path);
		$key  = array_splice($path, 0, 1);
		$key  = $key[0];

		//Select value
		if(empty($obj[$key]))
			return NULL;
			
		//Figure out if we recurse
		$val = $obj[$key];
		if(count($path))
			return $this->apath(implode('.', $path), $val);

		//If DataType then return
		if($val instanceof \Framework\Interfaces\IModelDataType)
		{
			var_dump($val);
			var_duMP("Code IModelDataType");
			die(__METHOD__);
			return $val->getValue();
		}

		return $val;
	}

	/******************************/
	/** END APath Implementation **/
	/******************************/

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
		//Check model type
		//if(array_key_exists($name):q
		//	ll
		$this->_data[$name] = $val;
	}

	/**
	 * Method retrieves the data associated with this model.
	 * @param $name The attribute name to retrieve.
	 */
	protected function _getData($name)
	{
		if(array_key_exists($name, $this->_data))
			return $this->_data[$name];

		return NULL;
	}

	/**
	 * Method removes the attribute from the model.
	 * @param $name The name of the attribute to remove.
	 */
	protected function _unsetData($name)
	{
		unset($this->_data[$name]);
	}

	/**
	 * Method determines if there is an attribute set.
	 * @param $name The attribute to check if it is set or not.
	 * @return Returns true if set else false.
	 */
	protected function _issetData($name)
	{
		return array_key_exists($name, $this->_data);
	}

	/********************************/
	/** END CObjectAccess Overload **/
	/********************************/

	/**
	 * Method sets model data and converts special cases to objects.
	 * @param $data The data to set.
	 */
	protected function _setModelData($data)
	{
		//Go through the data
		$data = (($data===NULL)? array() : $data);
		foreach($data as $key=>$val)
		{
			if(is_array($val))
			{
				//Associative array
				if(array_keys($val) !== range(0, count($val)-1))
				{
					die(__METHOD__ . " HASH!!");
				}
				else
					$this->_data[$key] = \Framework\Model\Structures\CArrayStructure::instantiate($val);
			}
			else
				$this->_data[$key] = $val;
		}
	}
}
?>
