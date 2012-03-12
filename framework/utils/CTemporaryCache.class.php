<?
class CTemporaryCache
{
	static public $_TMP_DIR = "/tmp/";
	private $_path;

	public function __construct($tmp)
	{
		$path = self::$_TMP_DIR . "/$tmp/";
		if(!file_exists($path))
			mkdir($path, 0777, true);

		$this->_path = realpath($path) . "/";
	}

	public function getContent($file)
	{
		$file = $this->_path . $file;
		if(!file_exists($file))
			return NULL;

		return file_get_contents($file);
	}

	public function putContent($file, $content, $flags=0)
	{
		$file = $this->_path . $file;
		$dir  = dirname($file);

		//Ensure directory exists
		if(!file_exists($dir))
			mkdir($dir, 0777, true);

		file_put_contents($file, $content, $flags);
	}

	public function delete($file)
	{
		$file = $this->_path . $file;

		if(file_exists($file))
			unlink($file);
	}
}
?>
