<?
namespace Framework\Parsers;

class CYAMLParser extends \Framework\Utils\CArrayObject
{
	private $_file;  //The file to load

	/**
	 * Constructor intializes the object with the file to use for loading.
	 * @param $file The file to load.
	 */
	public function __construct($file)
	{
		$this->_file = $file;
		if(!file_exists($this->_file))
			throw new \Exception("Unable to load YAML file '{$this->_file}'.");

		parent::__construct();
	}

	public function load()
	{
		$yaml = yaml_parse_file($this->_file);
		$this->_setData($yaml);
	}
}
?>
