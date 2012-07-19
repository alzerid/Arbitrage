<?
namespace Arbitrage2\Base;

class CFileSearchLoader
{
	private $_paths;

	public function __construct()
	{
		$this->_paths = array();
	}

	public function addPath($path, $throw=true)
	{
		if(!file_exists($path))
		{
			if($throw)
				throw new \EArbitrageException("Search path '$path' does not exist!");

			return false;
		}

		$path = realpath($path) . '/';
		$this->_paths[] = $path;
		
		return true;
	}

	public function loadFile($file, $throw=true)
	{
		foreach($this->_paths as $path)
		{
			$path .= $file;
			if(file_exists($path))
			{
				require_once($path);
				return true;
			}
		}

		if($throw)
			throw new \EArbitrageException("Unable to load file '$file'.");

		return false;
	}
}
?>
