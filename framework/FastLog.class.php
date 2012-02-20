<?
global $_conf;
define("FLOG_DIR", $_conf['approotpath'] . "log/");

class FastLog
{
	static function logit($type, $file, $body, $opts = FILE_APPEND)
	{
		//Check if the directory exists
		$dir = FLOG_DIR . "/$type";
		if(!file_exists($dir))
			mkdir($dir, 0777, true);
		
		//Get session id
		$sid = session_id();
		if($sid == "")
			$sid = "unknown";

		//Prepend date to log
		$date  = date("Y-m-d H:i:s");
		$body  = trim($body);
		$body  = "[$file][$date]:\n$sid\n$body\n";
		$body .= "=========================\n\n";

		//Save the contents
		$filename = FLOG_DIR . "/$type/{$sid}.txt";
		file_put_contents($filename, $body, $opts);
	}
}
?>
