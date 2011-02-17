<?
class MongoModel extends Model
{
	public function normalize()
	{
		if(array_key_exists('_id', $this->_variables))
			$this->_variables['_id'] = new MongoId($this->_variables['_id']);
	}

	public function fromForm($a)
	{
	}

	public function toForm()
	{
	}
}
?>
