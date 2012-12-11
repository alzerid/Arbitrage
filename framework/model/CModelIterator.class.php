<?
namespace Framework\Model;

class CModelIterator implements \Iterator
{
	private $_model;
	private $_array;
	private $_keys;
	private $_idx;

	/**
	 * Method constructs the CModelIterator object.
	 * @param \Framework\Model\CModel $model The model to associate the iterator to.
	 */
	public function __construct(\Framework\Model\CModel $model)
	{
		$this->_model = $model;
		$this->rewind();
	}

	/**
	 * Returns the current item in the iterator.
	 * @return mixed Return the value.
	 */
	public function current()
	{
		return $this->_array[$this->_keys[$this->_idx]];
	}

	/**
	 * Method returns the key of the current item.
	 * @return mixed Returns the key of the current item.
	 */
	public function key()
	{
		return $this->_keys[$this->_idx];
	}

	/**
	 * Method iterates the iterator to the next item in the array.
	 */
	public function next()
	{
		$this->_idx++;
	}

	/**
	 * Method resets the iterator to the beginning.
	 */
	public function rewind()
	{
		$this->_array = $this->_model->getData();
		$this->_keys  = array_keys($this->_array);
		$this->_idx   = 0;
	}

	/**
	 * Method checks if the current iterator is valid.
	 * @return boolean Returns true if the iterator is valid else fales.
	 */
	public function valid()
	{
		return array_key_exists($this->_idx, $this->_keys);
	}
}
?>
