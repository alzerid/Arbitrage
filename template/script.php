<?
if(php_sapi_name() != 'cli')
	return;

require_once('bootstrap.php');
require_once(Application::getConfig()->fwrootpath . 'include/ScriptApplication.class.php');
require_once(Application::getConfig()->fwrootpath . 'lib/common/ArgumentParser.class.php');

//Check for args
if($argc <= 1)
{
	help();
	die();
}

//Load script
$script = Application::getConfig()->approotpath . "app/scripts/{$argv[1]}/{$argv[1]}.php";
if(!file_exists($script))
{
	echo "Unable to find application script {$argv[1]}.\n";
	die();
}

//Add config property
Application::getConfig()->setVariable('scriptrootpath', dirname($script) . "/");

//Require it
require_once($script);

//Create an instance and run the script
$class  = ScriptApplication::getClassName($argv[1]);
$script = new $class(array_slice($argv, 2));

//Run the script
$script->run();

function help()
{
	echo "Arbitrage script runner.\n";
	echo "Usage: php script.php <script_name | bin_name> [params]\n\n";
	printf("%-25s%-15s%s\n", "Name", "Type", "Description");

	$glob = glob(Application::getConfig()->approotpath . "app/scripts/*", GLOB_ONLYDIR);
	foreach($glob as $script)
	{
		//Look for PHP
		$file  = "$script/" . basename($script);
		if(file_exists($file . ".php"))
		{
			$class = ucwords(preg_replace('/_/', ' ', basename($script)));
			$class = preg_replace('/ /', '', $class) . "Script";
			$type  = "php";

			require_once($file . ".php");
			$desc = ((isset($class::$DESCRIPTION))? $class::$DESCRIPTION : "UNKNOWN");
		}
		else if(file_exists($file . ".bin"))
		{
			$type = "bin";
			$desc = "Unknown";
		}
		else
		{
			$type = "unknown";
			$desc = "Unknown";
		}

		printf("%-25s%-15s%s\n", basename($script), $type, $desc);
	}
}
?>
