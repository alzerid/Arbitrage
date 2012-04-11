<?
class States
{
	static public $_NAMES_TO_ABBR;
	static public $_ABBR_TO_NAMES;

	static public function toName($abbr)
	{
		$abbr = strtolower($abbr);
		return ((isset(self::$_ABBR_TO_NAMES[$abbr]))? self::$_ABBR_TO_NAMES[$abbr] : NULL);
	}

	static public function toAbbreviation($name)
	{
		//Check if already in abbreviation mode
		$name = strtolower($name);
		if(isset(self::$_ABBR_TO_NAMES[$name]))
			return $name;
		
		return ((isset(self::$_NAMES_TO_ABBR[$name]))? self::$_NAMES_TO_ABBR[$name] : NULL);
	}

	static public function getNames()
	{
		return self::getStateNames();
	}

	static public function getStateNames()
	{
		return array_keys(self::$_NAMES_TO_ABBR);
	}

	static public function getAbbreviations()
	{
		return array_keys(self::$_ABBR_TO_NAMES);
	}
}

States::$_NAMES_TO_ABBR = array(
	'alabama'=>'al',
	'alaska'=>'ak', 
	'arizona'=>'az', 
	'arkansas'=>'ar', 
	'california'=>'ca', 
	'colorado'=>'co', 
	'connecticut'=>'ct', 
	'delaware'=>'de', 
	'district of columbia'=>'dc', 
	'florida'=>'fl', 
	'georgia'=>'ga', 
	'hawaii'=>'hi', 
	'idaho'=>'id', 
	'illinois'=>'il', 
	'indiana'=>'in', 
	'iowa'=>'ia', 
	'kansas'=>'ks', 
	'kentucky'=>'ky', 
	'louisiana'=>'la', 
	'maine'=>'me', 
	'maryland'=>'md', 
	'massachusetts'=>'ma', 
	'michigan'=>'mi', 
	'minnesota'=>'mn', 
	'mississippi'=>'ms', 
	'missouri'=>'mo', 
	'montana'=>'mt',
	'nebraska'=>'ne',
	'nevada'=>'nv',
	'new hampshire'=>'nh',
	'new jersey'=>'nj',
	'new mexico'=>'nm',
	'new york'=>'ny',
	'north carolina'=>'nc',
	'north dakota'=>'nd',
	'ohio'=>'oh', 
	'oklahoma'=>'ok', 
	'oregon'=>'or', 
	'pennsylvania'=>'pa', 
	'rhode island'=>'ri', 
	'south carolina'=>'sc', 
	'south dakota'=>'sd',
	'tennessee'=>'tn', 
	'texas'=>'tx', 
	'utah'=>'ut', 
	'vermont'=>'vt', 
	'virginia'=>'va', 
	'washington'=>'wa', 
	'west virginia'=>'wv', 
	'wisconsin'=>'wi', 
	'wyoming'=>'wy');

//Swap
States::$_ABBR_TO_NAMES = array_combine(array_values(States::$_NAMES_TO_ABBR), array_keys(States::$_NAMES_TO_ABBR));
?>
