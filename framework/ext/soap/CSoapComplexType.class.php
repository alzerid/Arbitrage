<?
abstract class CSoapComplexType
{
	public function __construct($vars)
	{
		$this->setVariables($vars);
	}

	public function setVariables($vars)
	{
		foreach($vars as $key=>$val)
			$this->$key = $val;
	}

	public function toSoapVar()
	{
		$vars = get_object_vars($this);
		$ret  = array();
		foreach($vars as $key=>$val)
			$ret[$key] = $val;

		$class     = get_called_class();
		$namespace = preg_replace('/\\\/', '.', preg_replace('/\\\[^\\\]+$/mis', '', $class));
		$class     = preg_replace('/.*\\\([^\\\]+)$/mis', '$1', $class);

		return new SoapVar($ret, SOAP_ENC_OBJECT, $class, "http://$namespace");
	}
}
?>
