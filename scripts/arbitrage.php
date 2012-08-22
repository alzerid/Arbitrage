<?

class CmdCreateProject extends \Framework\CLI\CArgumentCommand
{
	public function __construct()
	{
		parent::__construct('project', 'Command that creates an Arbitrage skeleton for new projects.');
	}

	public function execute()
	{
		//Set default values
		$dir = ARBITRAGE2_FW_PATH;

		//Grab folder
		printf("Create New Arbitrage Project\n");
		printf("Where to create project [$dir]: ");



	}

	public function help()
	{
		die('CmdCreateProject::help');
	}
}

class CmdCreate extends \Framework\CLI\CArgumentCommandParent
{
	public function __construct()
	{
		parent::__construct('create', 'Command creates projects.', array(new CmdCreateProject));
	}

	public function execute()
	{
		$this->_application->help();
	}

	public function help()
	{
	}
}

//Script class
class Arbitrage extends \Framework\Base\CCLIApplication
{

	public function initialize()
	{
		parent::initialize();

		//Set description
		$this->_description = "Arbitrage base script that creates new Arbitrage projects.";

		//Create argument commands
		$arguments   = array();
		$arguments[] = new CmdCreate;

		//Set application
		foreach($arguments as $argument)
			$argument->setApplication($this);
		
		//Parse arguments
		$this->_arguments = new \Framework\CLI\CArgumentParser($arguments);
	}

	public function run()
	{
		//Parse the arguments
		$this->_arguments->executeParse();
	}

	public function help()
	{
		printf("Usage: ./script.php %s [args1,args2...]\n\n", preg_replace('/\.php$/', '', basename(__FILE__)));
		printf("%-15s %s\n", "Command", "Description");

		foreach($this->_arguments->getArguments() as $argument)
			printf("%-15s %s\n", $argument->getCommand(), $argument->getDescription());
		
		printf("\n");
	}
}
?>
