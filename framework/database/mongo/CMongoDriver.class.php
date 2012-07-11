<?
class CMongoDriver extends CDatabaseDriver
{
	static public function getHandle($config)
	{
		var_dump("CONFIG", $config);
		die();
	}
}
?>
