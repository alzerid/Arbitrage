<?php
namespace Framework\CLI\ArgumentParser;

/**
 * Class used when commands are used for arguments instead of options.
 */
abstract class CCommandParentArgument extends CCommandArgument implements IArgumentCommandParent
{
	protected $_children;     //Child commands associated with this object

	/**
	 * Constructor creates the object.
	 * @param $command The command to assign this object to.
	 * @param $description The description of the command.
	 * @param $children The child commands associated with this object.
	 */
	public function __construct($command, $description, $children=array())
	{
		parent::__construct($command, $description);

		//Iterate and add
		$this->_children = array();
		foreach($children as $child)
			$this->_children[$child->getCommand()] = $child;
	}

	/**
	 * Parse the command.
	 * @param $args The argument list to parse out.
	 */
	public function parse(array $args)
	{
		//Check if $args[0] is in parent as a child
		if(isset($args[0]) && $this->childCommandExists($args[0]))
		{
			$command = $this->getChildCommand($args[0]);

			unset($args[0]);
			$args = array_values($args);
			$command->parse($args);
		}
		else
			parent::parse($args);
	}


	/**
	 * Method adds a child to the command object.
	 * @param \Framework\CLI\CCommandArgument $command The command to add to this object.
	 */
	public function addChildCommand(\Framework\CLI\ICommandArgument $command)
	{
		$this->_children[$command->getCommand()] = $command;
	}

	/**
	 * Get the child command,
	 * @return Returns the child command.
	 */
	public function getChildCommand($command)
	{
		return ((isset($this->_children[$command]))? $this->_children[$command] : NULL);
	}

	/**
	 * Checks if a child command exists within this object.
	 * @param $command The command to search for.
	 * @return Returns true if the command exists else false.
	 */
	public function childCommandExists($command)
	{
		return isset($this->_children[$command]);
	}

	/**
	 * Retuns child command information
	 * @return array Returns an array of commands and description.
	 */
	public function childHelp()
	{
		die('child help');
	}
}


?>
