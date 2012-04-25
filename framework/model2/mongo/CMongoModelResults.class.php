<?
namespace Arbitrage2\Model2;

/* Mongo */
class CMongoModelResults extends CModelResults
{
	/* Iterator */
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
		return $this->_results->rewind();

	}

	public function valid()
	{
		return $this->_results->valid();
	}
	/* End Iterator */
}
?>
