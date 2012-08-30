<?
namespace Framework\Database\Drivers\Mongo;

class CDatabaseDriver extends \Framework\Database\CDatabaseDriver
{
	public function __construct($config)
	{
		parent::__construct($config);

		//Connect
		$uri           = "mongodb://" . ((isset($config['host']))? $config['host'] : '127.0.0.1') . ':' . ((isset($config['port']))? $config['port'] : 27017);
		$this->_handle = new \Mongo($uri);
	}

	public function getQuery($class)
	{
		return new CMongoModelQuery($this, $class);
	}

	public function getBatch()
	{
		die('Mongo\CDatabaseDriver::getBatch');
	}
}
?>
