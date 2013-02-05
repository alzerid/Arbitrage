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

				//Get results
				$results = $handle->$database->$table->$action($condition);

				//Check for limits
				if($this->_limit !== NULL)
					$results->limit($this->_limit);

				//Sorting options
				if($this->_sort !== NULL)
					$restuls->sort($this->_sort);

				return $results;

			case "findOne":

				//Get results
				$result = $handle->$database->$table->$action($condition);
				return $result;

			case "save":
				$handle->$database->$table->$action($this->_data);
				return;

			case "remove":
				$handle->$database->$table->$action($condition);
				return;

			case "insert":
			case "update":
			case "upsert":
				throw new \Framework\Exceptions\ENotImplementedException("$action not implemented.");
		
			//TODO: Insert, update, upsert --EMJ
		}

		throw new \Framework\Exceptions\EDatabaseDriverException("Unknown action '$action'.");
	}

	/**
	 * Method creates the collection.
	 * @param $results The results to associate the collection to.
	 * @return \Framework\Database2\Model\CCollectionModel Returns the collection.
	 */
	/*public function createCollection($results)
	{
		return new \Mongo
		die(__METHOD__);
	}*/

	//TODO: Abstract method _convert* see mongo/CQueryModel
}
?>
