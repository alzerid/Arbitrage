<?php
namespace Framework\Database2\Drivers\Mongo;

class CQueryDriver extends \Framework\Database2\Drivers\CQueryDriver
{
	/**
	 * Method executes the query.
	 */
	public function execute()
	{
		$database  = $this->_database;
		$table     = $this->_table;
		$condition = (($this->_condition === NULL)? array() : $this->_condition);
		$action    = $this->_action;
		$handle    = $this->_driver->getHandle();

		//Execute
		switch($action)
		{
			case "findAll":
				$action = "find";

			case "findOne":
				
				//Get results
				$results = $handle->$database->$table->$action($condition);

				//Check for limits
				if($this->_limit !== NULL)
					$results->limit($this->_limit);

				//Sorting options
				if($this->_sort !== NULL)
					$restuls->sort($this->_sort);

				//TODO: Return the results in a collection
				return $results;
		
			//TODO: Insert, update, upsert --EMJ
		}

		throw new \Framework\Exceptoins\EDatabaseException("Unknown action '$action'.");
	}
}
?>
