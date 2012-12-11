<?
namespace Framework\Model;

class CModel extends \Framework\Utils\CObjectAccess
{
	protected $_data;

	/**
	 * Constructor for the model class.
	 * @param $data The variables to set as default data for this Model.
	 */
	public function __construct($data=NULL)
	{
		$this->_data = array();
		$this->_setModelData($data);
	}

	/**
	 * Method sets the value via apath notation.
	 * @param $path The path to set.
	 * @param $value The value to set.
	 * @param \Framework\Utils\CArrayObject $obj The object to traverse.
	 * @return mixed Returns a value or a \Framework\Utils\CArrayObject.
	 */
	public function setAPathValue($path, $value, \Framework\Utils\CObjectAccess $obj=NULL)
	{
		if($obj === NULL)
		{
			$this->setAPathValue($path, $value, $this);
			return;
		}

		//Get key
		$path = explode('.', $path);
		$key  = array_splice($path, 0, 1);
		$key  = $key[0];

		//Check if we continue the path
		if(!isset($obj[$key]) && count($path))
		{
			$class     = get_called_class();
			$obj[$key] = new $class;
		}

		//Set values
		if(count($path))
		{
			$path      = implode('.', $path);
			$obj[$key]->setAPathValue($path, $value, $obj[$key]);
		}
		else
			$obj[$key] = $value;
	}

	/**
	 * Method returns the value via apath notation.
	 * @param $path The path to set.
	 * @param \Framework\Utils\CArrayObject $obj The object to traverse.
	 * @return mixed Returns a value or a \Framework\Utils\CArrayObject.
	 */
	public function getAPathValue($path, \Framework\Utils\CObjectAccess $obj=NULL)
	{
		return $this->apath($path);
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
		if(!isset($obj->$key))
			return NULL;
			
		//Figure out if we recurse
		$val = $obj[$key];
		if(count($path))
			return $this->apath(implode('.', $path), $val);

		//If DataType then return
		if($val instanceof \Framework\Interfaces\IModelDataType)
			return $val->getValue();

		return $val;
	}

	/******************************/
	/** END APath Implementation **/
	/******************************/

	/**
	 * Method returns the raw data.
	 * @return array Returns the raw data.
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Method returns the array key value pairs.
	 */
	public function toArray()
	{
		$ret = array();
		foreach($this->_data as $key=>$data)
		{
			if($data instanceof \Framework\Model\CModel)
				$ret[$key] = $data->toArray();
			else
				$ret[$key] = $data;
		}

		return $ret;
	}

	/**
	 * Method retuns the iterator for this Model.
	 * @return \Framework\Model\CModelIterator Returns the iterator for this model.
	 */
	public function getIterator()
	{
		return new \Framework\Model\CModelIterator($this);
	}

	/**
	 * Method flattens the model array.
	 * @param $arr_key The current key.
	 * @param $obj The model object to set.
	 * @return \Framework\Form\CFormModel Returns the newly created flattened CFormModel.
	 */
	public function flatten($arr_key=NULL, $obj=NULL)
	{
		//If object is not set, set it
		if($obj===NULL)
		{
			$class = get_called_class();
			$obj   = new $class;

			return $this->flatten($arr_key, $this);
		}

		//Iterate through object
		$iterator = $obj->getIterator();
		$ret      = array();
		foreach($iterator as $key=>$value)
		{
			//Set array key
			$element = $this->getElement($key);
			$key     = ((!empty($arr_key))? "$arr_key.$key" : $key);

			//Check to see if it is a subform
			if($value instanceof \Framework\Model\CModel)
				$ret = array_merge($ret, $value->flatten($key));
			elseif(is_array($value))
			{
				$temp        = new \Framework\Model\CModel();
				$temp->_data = $value;
				$ret         = array_merge($ret, $temp->flatten($key, $temp));
			}
			else
			{
				//$obj->setAPathValue($key, $value);
				echo "Simple set: ";
				var_dump($arr_key, $key, $value, $element);
				var_dump($obj);
				die(__METHOD__);
			}
		}

		return $ret;
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
					$this->_data[$key] = $val;
					var_dump($key, $val, $this->_data);

					throw new \Framework\Exceptions\ENotImplementedException("HASH/CModel logic not implemented");
				}
				else
					$this->_data[$key] = new \Framework\Model\Structures\CArrayStructure($val);
			}
			else
				$this->_data[$key] = $val;
		}
	}
}
?>
