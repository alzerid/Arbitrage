<?
namespace Framework\Forms;

class CFormModel extends \Framework\Model\CModel
{
	/**
	 * Method converts the FormModel values to a \Framework\Interface\IModel.
	 * @param $model The arbitrage namespace of the model to convert to.
	 */
	public function convertToDatabaseModel($model)
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
		return $model::model($this->_data);
	}
}
?>
