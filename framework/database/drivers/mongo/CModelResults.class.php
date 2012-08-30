<?
namespace Framework\Database\Drivers\Mongo;

/* Mongo */
class CModelResults extends \Framework\Database\CModelResults
{
	private $_executed;
	private $_raw;

	public function __construct(\Framework\Database\CDriverQuery $query)
	{
		parent::__construct($query);
		$this->_executed = false;
		$this->_raw      = NULL;
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
		//Check if we even queried
		if($this->_results == NULL)
			$this->_executeQuery();

		if($this->_results != NULL)
		{
			if(!is_array($this->_results) && $this->_raw == NULL)
				$this->_raw = iterator_to_array($this->_results, false);
			elseif(is_array($this->_results))
				$this->_raw = $this->_results;

			if(!isset($this->_raw[$offset]))
				return NULL;

			return$this->_getModel($this->_raw[$offset]);
		}

		return NULL;
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
		$this->_executeQuery();
		if(!$this->_results)
			return 0;
		elseif(is_array($this->_results))
			return count($this->_results);

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
		if($this->_results != NULL || $this->_executed)
			return;

		//Query
		$this->_results  = $this->_query->execute($this);
		$this->_executed = true;
	}
}
?>
