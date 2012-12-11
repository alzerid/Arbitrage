<?
abstract class CCLIBaseApplication
{
	protected $_description;

	public function getApplicationDescription()
	{
		return $this->_description;
	}

	public function getApplicationName()
	{
		return strtolower(preg_replace('/Application$/', '', get_class($this)));
	}

	abstract public function getApplicationType();
}
?>
