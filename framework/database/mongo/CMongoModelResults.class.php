<?
namespace Framework\Database;

/* Mongo */
class CMongoModelResults extends CModelResults
{
	private $_raw;

	public function __construct(\Framework\Database\CModelQuery $query)
	{
		parent::__construct($query);
		$this->_raw = NULL;
	}

	/* Array Access */
	public function offsetExists($offset)
	{
		if($this->_raw == NULL)
			$this->_raw = iterator_to_array($this->_results, false);

		return isset($this->_raw[$offset]);
	}

	public function offsetGet($offset)
	{
		if($this->_raw == NULL)
			$this->_raw = iterator_to_array($this->_results, false);

		if(!isset($this->_raw[$offset]))
			return NULL;

		return$this->_getModel($this->_raw[$offset]);
	}

	public function offsetSet($offset, $value)
	{
		throw new \EArbitrageException("Unable to set offset for Model Results.");
	}

	public function offsetUnset($offset)
	{
		throw new \EArbitrageException("Unable to unset offset for Model Results.");
	}
	/* End Array Access */

	/* Iterator */
	public function count()
	{
		return $this->_results->count();
	}

	public function current()
	{
		return $this->_getModel($this->_results->current());
	}

	public function key()
	{
		return $this->_results->key();
	}

	public function next()
	{
		return $this->_results->next();

	}

	public function rewind()
	{
		$this->_executeQuery();
		return $this->_results->rewind();
	}

	public function valid()
	{
		return $this->_results->valid();
	}
	/* End Iterator */

	
	private function _executeQuery()
	{
		if($this->_results != NULL)
			return;

		//Query
		$this->_results = $this->_query->execute($this);
	}
}
?>
