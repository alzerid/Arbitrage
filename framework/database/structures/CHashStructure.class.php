<?
namespace Framework\Database\Structures;

//TODO: Code CLASS strictness when setting a CHashStructure with a class assigned to it --EMJ

class CHashStructure extends \Framework\Model\CMomentoModel implements \Framework\Interfaces\IDatabaseModelStructure
{
	protected $_driver;
	protected $_class;

	public function __construct($class=NULL, $data=NULL)
	{
		if($class instanceof \Framework\Database\Structures\CHashStructure)
		{
			$data  = $class;
			$class = $data->getClass();
		}
		elseif($class !== NULL)
			$class = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($class);

		$this->_class = $class;
		parent::__construct($data);
	}

	/**
	 * Method returns the updated query.
	 * @return array Retuns an array of the updated items.
	 */
	public function getUpdateQuery($pkey=NULL)
	{
		throw new \Framework\Exceptions\EModelStructureException("Unable to get query without specific driver structure.");
	}

	/**
	 * Method returns the query expression.
	 * @return array Returns an array of the items.
	 */
	public function getQuery()
	{
		throw new \Framework\Exceptions\EModelStructureException("Unable to get query without specific driver structure.");
	}

	/**
	 * Method sets the driver being used.
	 * @param \Framework\Interfaces\IDatabaseDriver $driver The driver to set to.
	 */
	public function setDriver(\Framework\Interfaces\IDatabaseDriver $driver=NULL)
	{
		$this->_driver = $driver;
	}

	/**
	 * Method returns the class associated with this hash structure.
	 * @return Returns the class.
	 */
	public function getClass()
	{
		return $this->_class;
	}

	/**
	 * Method sets model data and converts special cases to objects.
	 * @param $data The data to set.
	 */
	protected function _setModelData($data)
	{
		if($data === NULL)
			return;

		foreach($data as $key=>$val)
		{
			if(is_array($val))
			{
				//Get class
				$class = $this->_class;
				if($class === NULL)
					throw new \Framework\Exceptions\EModelDataTypeException("Unknown class type!");

				//Create class
				if(preg_match('/Framework\\\Database\\\Structures\\\CHashStructure/', $class))
					$this->_data[$key] = new $class(NULL, $val);
				else
				{
					$this->_data[$key] = new $class($val);
					$this->_data[$key]->merge();
				}

				//Set driver for Model and Structures
				if(is_subclass_of($class, "\\Framework\\Interfaces\\IDatabaseModelStructure", true))
					$this->_data[$key]->setDriver($this->_driver);
			}
			else
				$this->_data[$key] = $val;
		}
	}

	/*protected function _setModelData($data)
	{
		//TODO: Types cast everything

		//Set data to defaults
		$this->_data = static::defaults();
		$types       = static::types();
		$data        = (($data===NULL)? array() : $data);
		foreach($this->_data as $key=>$val)
		{
			if(!array_key_exists($key, $data) || $data[$key] === NULL)
				continue;

			//Check to see what type we are
			if($this->_data[$key] instanceof \Framework\Database\CModel)
			{
				$model = $this->_data[$key];
				$model->setDriver($this->_driver);
				$model->_setModelData($data[$key]);
			}
			elseif($this->_data[$key] instanceof \Framework\Interfaces\IDatabaseModelStructure)
			{
				//Get struct
				$struct = $this->_data[$key];
				$struct->setDriver($this->_driver);
				$struct->_setModelData($data[$key]);

				//Convert to model structure
				if($this->_driver)
					$this->_data[$key] = $this->_driver->convertNativeStructureToModelStructure($struct);
			}
			elseif($this->_data[$key] instanceof \Framework\Interfaces\IModelDataType)
			{
				if($this->_driver)
					$this->_variables[$key] = $this->_driver->convertNativeDataTypeToModelDataType($data[$key]);
				else
				{
					$this->_variables[$key] = clone $this->_data[$key];
					$this->_variables[$key]->setValue($data[$key]);
				}
			}
			else
				$this->_variables[$key] = $data[$key];
		}
	}*/
}
?>
