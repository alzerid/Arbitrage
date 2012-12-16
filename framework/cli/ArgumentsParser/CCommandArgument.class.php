<?php
namespace Framework\CLI\ArgumentParser;

/**
 * Class used when commands are used for arguments instead of options.
 */
abstract class CCommandArgument implements ICommandArgument
{
	protected $_command;      //Command associated with this object
	protected $_description;  //Description of the command
	protected $_application;  //Application assigned to this command

	/**
	 * Constructor creates the object.
	 * @param $command The command to assign this object to.
	 */
	public function __construct($command, $description)
	{
		$this->_command     = $command;
		$this->_description = $description;
	}

	/**
	 * Method that returns the command associated to this ICommandArgument class.
	 * @returns string The command associated to this class.
	 */
	public function getCommand()
	{
		return $this->_command;
	}

	/**
	 * Method returns the description of the command.
	 * @return string Returns the description.
	 */
	public function getDescription()
	{
		return $this->_description;
	}

	/**
	 * Parse the command.
	 * @param $args The argument list to parse out.
	 */
	public function parse(array $args)
	{
		$this->execute(implode(' ', $args));
	}

	/**
	 * Sets the application for this command object.
	 * @param \Framework\Base\CCLIApplication $application The application to set.
	 */
	public function setApplication(\Framework\Base\CCLIApplication $application)
	{
		$this->_application = $application;
	}
}

?>
