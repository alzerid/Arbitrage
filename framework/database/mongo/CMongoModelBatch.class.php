<?
namespace Framework\Database;

class CMongoModelBatch extends CMongoModelQuery
{
	public function execute()
	{
		//Execute command
		$class = $this->_class;
		$prop  = $class::properties();

		//Query
		//TODO: DB Factory
		$handle = new \Mongo('mongodb://localhost:27017', array('persist' => 'php'));
		$handle = $handle->{$prop['database']}->{$prop['table']};

		if($this->_cmd == "insert")
		{
			$ret = $handle->batchInsert($this->_data);
			$list = new CMongoModelResults($this->_data, $class);
			return $list;
		}
		elseif($this->_cmd == 'update')
		{
			//Setup update
			$update = new \CArrayObject($this->_data);
			$update = array('$set' => $update->flatten(0)->toArray());

			//Setup conditions
			$query = new \CArrayObject($this->_query);
			$query = $query->flatten()->toArray();

			//Update
			$ret = $handle->update($query, $update, array('multiple' => true));
		}

		else
			throw new EModelException("Cannot do batch operation on '{$this->_cmd}'.");
	}
}
?>
