<?
class URL
{
	static public function normalize($url)
	{
		if(is_array($url))
		{
			$url = $url[0];
			return($url);
		}
		
		return $url;
	}
}
?>
