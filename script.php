<?
//Bootstrap this script with script.php
$_app = 
define("ARBITRAGE2_FW_PATH", ((isset($_SERVER['ARBITRAGE2_FW_PATH']))? $_SERVER['ARBITRAGE2_FW_PATH'] : "/domain/arbitrage/"));
define("ARBITRAGE2_FW_VERSION", "3.0");

//Pre boot files that are needed before application boot
require_once(ARBITRAGE2_FW_PATH . 'framework/Interfaces.class.php');            //Interfaces file full of Interface definitions
require_once(ARBITRAGE2_FW_PATH . 'framework/base/CKernel.class.php');          //Main Arbitrage entry class

$script_path = dirname(__FILE__) . "/scripts";
\Framework\Base\CKernel::getInstance()->bootstrap();
\Framework\Base\CKernel::getInstance()->registerPackagePath($script_path);

//Check to see which application to run
$cli = NULL;

//TODO: Create CLI
if(isset($argv[1]))
	$cli = \Framework\Base\CKernel::getInstance()->createCLIApplication($argv[1]);

if($cli)
	$cli->run();
else
{
	//Print help
	printf("Arbitrage script loader.\n");
	printf("Usage: ./script.php <application> [args1, args2...]\n\n");
	printf("%-15s %s\n", "Application", "Description");
	printf("%s\n", str_repeat('-', 30));

	$glob = glob($script_path . "/*.php");
	foreach($glob as $file)
	{
		$name = preg_replace('/\.php$/', '', basename($file));
		$app  = \Framework\Base\CKernel::getInstance()->createCLIApplication($name);

		printf("%-15s %s\n", $name, $app->getDescription());
	}

	printf("\n");
}
?>
