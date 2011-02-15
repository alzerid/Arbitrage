<?
class Language
{
	protected $i18n;
	public $phoneFields;
	public $name;

	public function _($l)
	{
		return $this->get($l);
	}

	public function get($l)
	{
		if(array_key_exists($l, $this->i18n))
			return $this->i18n[$l];

		return null;
	}
}

class LanguageFactory
{
	static function getLanguage($module, $lang=null)
	{
		global $_conf;

		if($lang == null)
		{
			//Set default locale
			$locale   = "en";
			$language = "en";
			$country  = "us";
		}
		else
		{
			$ex = explode("_", $lang);
			$locale   = $ex[0];
			$language = $ex[0];
			$country  = $ex[1];
		}	

		//Get object
		$full_path = "{$_conf['fsrootpath']}/modules/$module/i18n/$lang.php";
		if(file_exists($full_path))
			require_once($full_path);
		else
		{
			trigger_error("Unable to grab language '$lang'.", E_USER_ERROR);
			die();
		}

		$lang = new PathLanguage();

		return $lang;
	}
}
?>
