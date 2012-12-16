<?php
namespace Framework\CLI;

abstract class CCLIApplication extends \Framework\Base\CApplication
{
	private $_description;  //Description
	private $_arguments;    //List of arguments

	/**
	 * Method initializes the script
	 */
	public function initialize()
	{
		//Call parent initialize
		parent::initialise();
		$this->_description = "UNKNOWN DESCRIPTION";

		//Add argument files
		CKernel::getInstance()->requireFrameworkFile('CLI.ArgumentParser.CArgumentParser');
		CKernel::getInstance()->requireFrameworkFile('CLI.ArgumentParser.CBaseArgument');
		CKernel::getInstance()->requireFrameworkFile('CLI.ArgumentParser.CBooleanArgument');
		CKernel::getInstance()->requireFrameworkFile('CLI.ArgumentParser.CValueArgument');
		CKernel::getInstance()->requireFrameworkFile('CLI.ArgumentParser.CMultipleArgument');
		CKernel::getInstance()->requireFrameworkFile('CLI.ArgumentParser.CRequiredArgument');
		CKernel::getInstance()->requireFrameworkFile('CLI.ArgumentParser.CCommandArgument');
		CKernel::getInstance()->requireFrameworkFile('CLI.ArgumentParser.CCommandParentArgument');

		//Initialize the arguments
		$this->_arguments = $this->_initializeArguments();
	}

	/**
	 * Method returns the description.
	 * @return Returns the description.
	 */
	public function getDescription()
	{
		return $this->_description;
	}

	/**
	 * Method prints the help menu.
	 */
	public function help()
	{
		static $format = "%-20s %-20s %-40s\n";

		//Print header
		printf($format, "Argument", "Value", "Description");
		printf("%s\n", str_repeat('=', 80);

		//Go through each argument and print help
		foreach($this->_arguments as $argument)
		{
			//Get argument details
			$short = $argument->getShortOpt();
			$long  = $argument->getLongOpt();
			$desc  = $argument->getDescription();
			$value = $argument->getValue();

			//Print the help
			$opt = "-$short" . (($long)? ",--$long": "");
			printf($format, $opt, $value, $desc);
		}

		//Extra newline
		printf("\n");
	}

	/**
	 * Method is the main entry point to call run and parse arguments.
	 */
	public function process()
	{
		die(__METHOD__);
	}

	/**
	 * Script main run method.
	 */
	abstract public function run();

	/** Overloaded Error Handling Methods **/

	/**
	 * Method handles errors.
	 */
	public function handleError(\Framework\Interfaces\IEvent $event)
	{
		//TODO: Handle Debug Mode Exceptions

		$config = $this->getConfig();
		$debug  = ((isset($config->arbitrage2->debugMode))? $config->arbitrage2->debugMode : false);

		$this->handleException($event);
	}

	/**
	 * Method intializes services specified in the application configuration file.
	 */
	public function handleException(\Framework\Interfaces\IEvent $event)
	{
		//TODO: Handle HTTP Exceptions
		//TODO: Handle Debug Mode Exceptions
		$service = CKernel::getInstance()->getService($this, 'errorHandler');
		if($service !== NULL)
			$service->handleEvent($event);
		else
			$this->_printEvent($event);

		$event->stopPropagation();
		$event->preventDefault();
	}
	/** End Overloaded Error Handling Methods **/


	/**
	 * Method initializes an argument.
	 * @return \Framework\CLI\Arguments\CArgumentParser Returns an argument parser.
	 */A
	protected function _initializeArguments()
	{
		//Create parser
		$parser = new \Framework\CLI\CArgumentParser;

		//Add parser
		$parser->addArgument(new CBooleanArgument('h', 'help', 'Print this help menu.'));

		return $parser;
	}

	/**
	 * Method prints out in HTML format the error or exception event.
	 * @param \Framework\Interfaces\IEvent $event The event to print out.
	 */
	private function _printEvent(\Framework\Interfaces\IEvent $event)
	{
		printf("Arbitrage: Global Exception Caught\n");
		printf("%s\n", str_repeat("=", 30));
		printf("Message: %s\n", $event->message);
		printf("Code: %d\n", $event->code);
		printf("File: %s\n", $event->file);
		printf("Line: %s\n\n", $event->line);
		
		//Trace
		printf("Trace\n");
		printf("%s\n", str_repeat('=', 30));
		$cnt = count($event->trace)-1;
		for($i=0; $i<$cnt; $i++)
		{
			printf("Trace #: %d\n", $i);
			printf("File: %s\n", $event->trace[$i]['file']);
			printf("Line #: %d\n\n", $event->trace[$i]['line']);
		}
	}
}
?>
