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

	/**
	 * Method converts the FormModel values to a \Framework\Interface\IModel.
	 * @param $model The arbitrage namespace of the model to convert to.
	 */
	/*public function convertToDatabaseModel($model)
	{
		$model = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($model);

		//Ensure model is loaded
		try
		{
			$instance = $model::load();
		}
		catch(\Exception $ex)
		{
			throw new \Framework\Exceptions\EArbitrageException("Model '$model' does not exist!");
		}

		//Ensure class inherits \Framework\Interfaces\IModel
		if(!($instance instanceof \Framework\Interfaces\IModel))
			throw new \Framework\Exceptions\EArbitrageException("Model '$model' does not inherit \\Framework\\Interfaces\\IModel.");

		//Convert to model specified in the parameter
		//TODO: Code conversion
		return $model::instantiate($this->_data);
	}*/
}
?>
