<?
abstract class CPHPApplication extends CCLIBaseApplication
{
	protected $_description;
	protected $_args;

	public function __construct($arguments=NULL)
	{
		//Auto add --help
		if($arguments === NULL)
			$arguments = array();

		$arguments[] = new CArgumentBoolean('h', 'help', false);
		$this->_args = new CArgumentParser($arguments);
	}

	public function process()
	{
		$this->_args->executeParse();
	}

	abstract public function help();

	public function getApplicationType()
	{
		return "PHP";
	}
}
?>
