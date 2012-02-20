<?
class Template
{
	static public function displayHeader()
	{
		global $_conf;

		$header = 'html/header.php';
		if(file_exists($header))
			require_once($header);
		else
			require_once($_conf['fsrootpath'] . "application/html/header.php");
	}

	static public function displayFooter()
	{
		global $_conf;

		$footer = 'html/footer.php';
		if(file_exists($footer))
			require_once($header);
		else
			require_once($_conf['fsrootpath'] . "application/html/footer.php");
	}

	static public function displayFile($file)
	{
		global $_conf;

		/* Casscading find, first find the template
			 usder <module>/templates folder then under the
		   under application/templates directory. */

		if(file_exists($file))
			require_once($file);
		elseif(file_exists("{$_conf['fsrootpath']}/application/templates/" . basename($file)) . ".php")
			require_once("{$_conf['fsrootpath']}/application/templates/" . basename($file) . ".php");
		elseif(file_exists("{$_conf['fsrootpath']}/application/templates/" . basename($file)))
			require_once("{$_conf['fsrootpath']}/application/templates/" . basename($file));
		else
			trigger_error("No template to show. \"$file\"");
	}
}
?>
