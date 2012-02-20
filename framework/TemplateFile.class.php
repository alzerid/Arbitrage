<?
class TemplateFile
{
	static function loadFile($file, $replacements=NULL)
	{
		$file = Application::getConfig()->approotpath . "app/templates/$file";
		return self::loadFileAbsolute($file, $replacements);
	}

	static function loadFileAbsolute($file, $replacements)
	{
		$ret  = file_get_contents($file);
		if($replacements != NULL)
			$ret = TemplateFile::replace($ret, $replacements);

		return $ret;
	}

	static public function replace($input, $replacements)
	{
		foreach($replacements as $key=>$value)
		{
			$key   = preg_quote((string) $key);
			$input = preg_replace('/\{\{' . $key . '\}\}/U', $value, $input);
		}

		return $input;
	}
}
?>
