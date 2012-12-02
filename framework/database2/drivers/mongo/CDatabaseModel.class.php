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
}
?>
