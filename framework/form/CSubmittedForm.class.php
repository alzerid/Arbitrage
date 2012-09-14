<?
namespace Framework\Form;

class CSubmittedForm
{
	private $_form;

	public function __construct(\Framework\Form\CForm $form)
	{
		$this->_form  = $form;
	}

	/**
	 * Returns the submitted form with values.
	 * @param $namespace The namespace to use to convert the form to.
	 * @return \Framework\Forms\CForm The CForm type object.
	 */
	static public function getSubmittedForm($namespace)
	{
		//First check to see if the form was indeed submitted
		$vars = \Framework\Base\CKernel::getInstance()->getApplication()->getController()->getRequest();
		$idx  = preg_replace('/\./', '_', $namespace);

		//Check if form was submitted
		if(empty($vars[$idx]))
			return NULL;

		//Check to see if the form is valid
		$vars  = $vars[$idx]->toArray();
		$class = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($namespace);

		//Check if submitted form is of CForm
		$form = NULL;
		if(class_exists($class) && is_a($class, "\\Framework\\Form\\CRenderableForm", true))
			$form = new $class;
		else
			$form = new \Framework\Form\CForm(array('id' => $idx));

		//Set variables
		$model = $form->getModel();
		foreach($vars as $key=>$val)
			$model[$key] = $val;

		//Create submitted form
		$submitted = new \Framework\Form\CSubmittedForm($form);

		return $submitted;
	}

	/**
	 * Method returns the form that the CSubmittedForm is wrapping.
	 * @return \Framework\Forms\CForm The CForm instance that is wrapped.
	 */
	public function getForm()
	{
		return $this->_form;
	}

	/**
	 * Method returns the model.
	 * @return \Framework\Model\CModel
	 */
	public function getModel()
	{
		return $this->_form->getModel();
	}
	
	/*public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->_form, $name), $arguments);
	}*/
}
?>
