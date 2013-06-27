<?
namespace Framework\Database2\Drivers\Mongo;

class CDatabaseModel extends \Framework\Model\CMomentoModel
{
	/**
	 * Method returns default properties for this driver model.
	 * @return array Returns an array of default properties.
	 */
	static public function properties()
	{
		return array('idKey' => '_id');
	}

	/**
	 * Method updates the data base entries from the model.
	 */
	 public function update()
	 {
		//We must have an ID associated
		//TODO: Use the $idKey!!
		$id = ((isset($this->_variables['_id']))? $this->_variables['_id'] : (($this->_data['_id'])? $this->_data['_id'] : NULL));
		if($id === NULL)
			throw new \Framework\Exceptions\EDatabaseDriverException("Model must contain an ID to update!");
		else if(isset($this->_variables['_id']))
			unset($this->_variables['_id']);

		//Get data
		$data = $this->_variables;
		if(count($data) == 0)
			return;

		//Update
		die(__METHOD__);
		$this->getQuery()->update(array('_id' => $id->getValue()), array('$set' => array($data)));
	 }
}
?>
