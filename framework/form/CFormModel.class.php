<?
namespace Framework\Form;

class CFormModel extends \Framework\Model\CModel
{
	public function __construct($data=NULL)
	{
		if($data instanceof \Framework\Model\CModel)
			$data = $data->_data;

		parent::__construct($data);
	}

	/**
	 * Method converts the model to a database model.
	 * @param $namespace The namespace model.
	 * @return \Framework\Database\CModel The database model or NULL.
	 */
	public function convertToModel($namespace)
	{
		$class = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($namespace);
		if(!class_exists($class))
			throw new \Framework\Exceptions\EModelException("Database model '$class' does not exist!");

		//Create object
		return new $class($this->toArray());
	}

	/**
	 * Method returns the array key value pairs.
	 */
	public function toArray()
	{
		//TODO: Code array value

		$ret = array();
		foreach($this->_data as $key=>$data)
		{
			if($data instanceof \Framework\Form\CForm)
				$ret[$key] = $data->getModel()->toArray();
			elseif($data instanceof \Framework\Form\Elements\CBaseFormElement)
				$ret[$key] = $data->getValue();
			elseif($data instanceof \Framework\Model\CModel)
				$ret[$key] = $data->toArray();
			else
				$ret[$key] = $data;
		}

		return $ret;
	}

	/**
	 * Method returns the actual element from the value model.
	 * @param $path The path where the element is located.
	 * @param $obj The array object to use.
	 * @return Returns the element.
	 */
	public function getElement($path, \Framework\Utils\CObjectAccess $obj=NULL)
	{
		if($obj===NULL)
			return $this->getElement($path, $this);

		//Get key
		$path = explode('.', $path);
		$key  = array_splice($path, 0, 1);
		$key  = $key[0];

		//Check if key even exists
		if(!isset($obj->_data[$key]))
			return NULL;

		//Return element
		if(count($path))
			return $this->getElement(implode('.', $path), $obj->$key);

		//Return element
		return $obj->_data[$key];
	}

	/**
	 * Method recursively sets the values from the input paramater.
	 * @param \Framework\Model\CModel $model The model to get the values from.
	 */
	public function setData(\Framework\Model\CModel $model)
	{
		$tmp = $this->flatten();
		foreach($tmp as $key=>$value)
		{
			$val = $model->apath($key);
			if($val !== NULL)
				$this->setAPathValue($key, $val);
		}
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
			if($value instanceof \Framework\Form\CForm)
				$ret = array_merge($ret, $value->getModel()->flatten($key));
			elseif($value instanceof \Framework\Model\CModel)
			{
				$value = new \Framework\Form\CFormModel($value);
				$ret = array_merge($ret, $value->flatten($key));
			}
			elseif($element instanceof \Framework\Interfaces\IFormElement)
				$ret[$key] = $element;
			elseif(is_array($value))
			{
				$temp        = new \Framework\Form\CFormModel();
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

	/**
	 * Method sets the data.
	 * @param $name The name of the attribute to set.
	 * @param $val The value to set the attribute to.
	 */
	protected function _setData($name, $val)
	{
		if(!array_key_exists($name, $this->_data))
			throw new \Framework\Exceptions\EFormException("Attribute '$name' not in form.");

		//Check what to do
		if($this->_data[$name] instanceof \Framework\Interfaces\IFormElement)
			$this->_data[$name]->setValue($val);
		else
			parent::_setData($name, $val);
	}

	/**
	 * Method gets the data
	 * @param $name The name of the attribute to get.
	 */
	protected function _getData($name)
	{
		if(!array_key_exists($name, $this->_data))
			throw new \Framework\Exceptions\EFormException("Attribute '$name' not in form.");
		
		//Check what to do
		if($this->_data[$name] instanceof \Framework\Interfaces\IFormElement)
			return $this->_data[$name]->getValue($name);
		elseif($this->_data[$name] instanceof \Framework\Model\CModel && !($this->_data[$name] instanceof \Framework\Form\CFormModel))
			$this->_data[$name] = new \Framework\Form\CFormModel($this->_data[$name]);

		return parent::_getData($name);
	}
}
?>
