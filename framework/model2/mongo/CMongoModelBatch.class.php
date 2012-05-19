<?
namespace Arbitrage2\Model2;

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
		else
			throw new EModelExceptoin("Cannot do batch operation on '{$this->_cmd}'.");
	}
}
?>
