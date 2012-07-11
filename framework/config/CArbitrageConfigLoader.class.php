<?
abstract class CArbitrageConfigLoader
{
	protected $_file;

	public function __construct($file)
	{
		$this->_file = $file;
	}

	static public function getLoader($filename)
	{
		//Determine if it is a YAML file or PHP file
		$file = basename($filename);
		$file = explode('.', $file);
		$ext  = strtolower($file[count($file)-1]);

		//Grab loader
		switch($ext)
		{
			case 'yaml':
			case 'yml':
				return new CArbitrageConfigLoaderYAML($filename);
				break;

			default:
				throw new EArbitrageException("Unknown config file format '$ext'.");
		}
	}

	abstract public function load(&$variables);

	protected function _extend(&$dst, $src)
	{
		//Go through each value
		foreach($src as $key => $val)
		{
			//Check fi dst exists
			if(!array_key_exists($key, $dst))
				$dst[$key] = array();

			//Check assignement
			if(is_array($val))
			{
				if($this->_isAssoc($val))
					$this->_extend($dst[$key], $val);
				else
					$dst[$key] = array_merge($dst[$key], $val);
			}
			else
				$dst[$key] = $val;
		}
	}

	private function _isAssoc($arr)
	{
		return array_values($arr) !== $arr;
	}
}

class CArbitrageConfigLoaderYAML extends CArbitrageConfigLoader
{
	public function load(&$variables)
	{
		$conf = yaml_parse_file($this->_file);
		$this->_extend($variables, $conf);
	}
}
?>
