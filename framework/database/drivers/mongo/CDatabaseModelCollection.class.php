<?
namespace Framework\Database\Drivers\Mongo;

/* Mongo */
class CDatabaseModelCollection extends \Framework\Database\CDatabaseModelCollection implements \ArrayAccess, \Iterator
{
	private $_cursor;
	private $_raw;

	public function __construct(\Framework\Database\CDriverQuery $query, $cursor)
	{
		parent::__construct($query);
		$this->_cursor   = $cursor;
		$this->_executed = false;
		$this->_raw      = NULL;
	}

	/* Array Access */
	public function offsetExists($offset)
	{
		if($this->_raw == NULL)
			$this->_raw = iterator_to_array($this->_cursor, false);

		return isset($this->_raw[$offset]);
	}

	public function offsetGet($offset)
	{
		if($this->_cursor != NULL)
		{
			if(!is_array($this->_cursor) && $this->_raw == NULL)
				$this->_raw = iterator_to_array($this->_cursor, false);
			elseif(is_array($this->_cursor))
				$this->_raw = $this->_cursor;

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
		if(!$this->_cursor)
			return 0;
		elseif(is_array($this->_cursor))
			return count($this->_cursor);

		return $this->_cursor->count();
	}

	public function current()
	{
		return $this->_getModel($this->_cursor->current());
	}

	public function key()
	{
		return $this->_cursor->key();
	}

	public function next()
	{
		return $this->_cursor->next();
	}

	public function rewind()
	{
		return $this->_cursor->rewind();
	}

	public function valid()
	{
		return $this->_cursor->valid();
	}
	/* End Iterator */
}
?>
