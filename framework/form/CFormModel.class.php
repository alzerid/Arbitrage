<?
namespace Framework\Forms;

class CFormModel extends \Framework\Model\CModel
{
	/**
	 * Method instantiates the model.
	 */
	static public function instantiate($data=NULL)
	{
		if($data instanceof \Framework\Model\CModel)
			$obj = parent::instantiate($data->_data);
		else
			throw new \Framework\Exceptions\EModelException("Unable to handle instantiation of the data.");

		return $obj;
	}

	/**
	 * Method converts the model to a database model.
	 * @param $namespace The namespace model.
	 * @param @return \Framework\Database\CModel The database model or NULl.
	 */
	public function convertToModel($namespace)
	{
		$class = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($namespace);
		if(!class_exists($class))
			throw new \Framework\Exceptions\EModelException("Database model '$class' does not exist!");

		//Create object
		$obj = $class::instantiate();
		foreach($this->_data as $key=>$val)
		{
			if(isset($obj->$key))
			{
				if($val instanceof \Framework\Form\Elements\CBaseFormElement)
					$val = $val->getValue();

				$obj->$key = $val;
			}
		}

		return $obj;
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
			if($data instanceof \Framework\Form\Elements\CBaseFormElement)
				$ret[$key] = $data->getValue();
			elseif($data instanceof \Framework\Model\CModel)
			{
				die(__METHOD__);
			}
			else
				$ret[$key] = $data;
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
		if($this->_data[$name] instanceof \Framework\Form\Elements\CBaseFormElement)
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
		if($this->_data[$name] instanceof \Framework\Form\Elements\CBaseFormElement)
			return $this->_data[$name]->getValue();

		return parent::_getData($name);
	}
}
?>
