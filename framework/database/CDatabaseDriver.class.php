<?
namespace Framework\Database;
abstract class CDatabaseDriver
{
	static public function getHandle($config)
	{
		throw new \Framework\Interfaces\EDatabaseDriverException("Your driver must implement ::getHandle.");
	}
}
?>
