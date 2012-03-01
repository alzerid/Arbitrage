<?
class CTemplateFile extends CTemplate
{
	public function __construct($file)
	{
		$this->load($file);
	}

	public function load($file)
	{
		$path = CApplication::getConfig()->_internals->approotpath . "app/templates/$file";
		if(!file_exists($path))
			throw new EArbitrageException("Unable to load template file '$file'.");

		$this->_contents = file_get_contents($path);
	}
}
?>
