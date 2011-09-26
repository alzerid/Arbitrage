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
			$input = preg_replace('/{{' . preg_quote($key) . '}}/', $value, $input);

		return $input;
	}
}
?>
