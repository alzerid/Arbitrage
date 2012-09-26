<?
namespace Framework\Database\Drivers\Mongo\Structures;

class CArrayStructure extends \Framework\Database\Structures\CArrayStructure
{
	/**
	 * Method returns the updated query.
	 * @return array Retuns an array of the updated items.
	 */
	public function getUpdateQuery()
	{
		//TODO: Code smarter differences
		if(count($this->_data) == 0)
			return $this->_data;

		$ret = array_diff($this->_data, $this->_originals);
		if(count($ret) == 0)
			return NULL;

		return $this->_data;
	}

	/**
	 * Method returns the query expression.
	 * @return array Returns an array of the items.
	 */
	public function getQuery()
	{
		return $this->_data;
	}
}
?>
