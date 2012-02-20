<?
class Language
{
	protected $_i18n;
	protected $_language;
	protected $_locale;

	public function __construct($file)
	{
		global $_conf;

		$lang            = explode('_', $file);
		$this->_language = strtolower($lang[0]);
		$this->_locale   = strtolower($lang[1]);
		$this->_i18n     = array();

		//Load up the file
		$i18n = &$this->_i18n;
		require_once($_conf['approotpath'] . "app/i18n/$file.i18n.php");
	}

	public function __get($var)
	{
		if(isset($this->_i18n) && isset($this->_i18n[$var]))
			return $this->_i18n[$var];

		return NULL;
	}

	public function getContent()
	{
		return $this->_i18n;
	}

	public function _($l)
	{
		return $this->get($l);
	}

	public function get($l)
	{
		if(array_key_exists($l, $this->_i18n))
			return $this->_i18n[$l];

		return null;
	}

	public function getLocale()
	{
		return $this->_locale;
	}

	public function getLanguage()
	{
		return $this->_language;
	}
}
?>
