<?
abstract class CCLIParentCommand extends CCLICommand
{
	abstract public function commands();

	public function isChildCommand($arguments)
	{
		return (count($arguments) > 0 && in_array($arguments[0], $this->commands()));
	}
}

abstract class CCLICommand
{
	public $command;
	public $arguments;

	public function __construct($cmd='', $arguments=array())
	{
		$this->command   = $cmd;
		$this->arguments = $arguments;
	}

	static public function getCommand($str)
	{
		$str   = preg_replace('/\s\s+/', '', trim($str));
		$args  = explode(' ', $str);
		$cmd   = $args[0];
		$args  = array_slice($args, 1);
		$class = $cmd . "Command";

		//Check if class exists
		if($cmd === "" || !class_exists($class))
			return new UnknownCommand($cmd, $args);


		//Create new command
		$command = new $class($cmd, $args);

		//Resursive if Instance of Subcommand
		while($command instanceof CCLIParentCommand && $command->isChildCommand($args))
		{
			$cmd    .= " {$args[0]}";
			$class   = preg_replace('/command$/i', '', $class) . "_" . $args[0] . "Command";
			$args    = array_slice($args, 1);

			//Create child command
			$command = new $class($cmd, $args);
		}
		return $command;
	}

	abstract function execute();
}

?>
