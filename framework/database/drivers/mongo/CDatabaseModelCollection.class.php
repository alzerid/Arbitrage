<?
namespace Framework\Database\Drivers\Mongo;

/* Mongo */
class CDatabaseModelCollection extends \Framework\Database\CDatabaseModelCollection
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
			$this->_raw = iterator_to_array($this->_collection, false);

		return isset($this->_raw[$offset]);
	}

	public function offsetGet($offset)
	{
		//Check if we even queried
		if($this->_collection == NULL)
			$this->_executeQuery();

		if($this->_collection != NULL)
		{
			if(!is_array($this->_collection) && $this->_raw == NULL)
				$this->_raw = iterator_to_array($this->_collection, false);
			elseif(is_array($this->_collection))
				$this->_raw = $this->_collection;

			if(!isset($this->_raw[$offset]))
				return NULL;

			return $this->_getModel($this->_raw[$offset]);
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
		if(!$this->_collection)
			return 0;
		elseif(is_array($this->_collection))
			return count($this->_collection);

		return $this->_collection->count();
	}

	public function current()
	{
		return $this->_getModel($this->_collection->current());
	}

	public function key()
	{
		return $this->_collection->key();
	}

	public function next()
	{
		return $this->_collection->next();
	}

	public function rewind()
	{
		$this->_executeQuery();
		return $this->_collection->rewind();
	}

	public function valid()
	{
		return $this->_collection->valid();
	}
	/* End Iterator */

	
	private function _executeQuery()
	{
		if($this->_collection != NULL || $this->_executed)
			return;

		//Query
		$this->_collection = $this->_query->execute($this);
		$this->_executed   = true;
	}
}
?>
