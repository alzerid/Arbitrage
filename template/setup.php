#!/bin/env /usr/bin/php
<?
if(php_sapi_name() != 'cli')
	return;

require_once('bootstrap.php');
require_once(Application::getConfig()->fwrootpath . 'include/ScriptApplication.class.php');
require_once(Application::getConfig()->fwrootpath . 'lib/common/ArgumentParser.class.php');

abstract class SetupCommand
{
	private $_name;
	protected $_cmds;
	protected $_description;
	static private $_CONFIG = array();

	public function __construct($name, $desc)
	{
		$this->_name        = $name;
		$this->_description = $desc;
	}

	static public function loadConfigs()
	{
		$config = Application::getConfig();
		$file   = $config->approotpath . ".setup";
		$files  = Application::recursiveGlob($file);

		foreach($files as $file)
			self::$_CONFIG[dirname($file)] = yaml_parse_file($file);
	}


	public function help()
	{
		printf("Help Menu - %s SubCommands\n", $this->_name);
		printf("php script.php %s <sub command>\n", $this->_name);
		printf("--------------------------------------\n");
		foreach($this->_cmds as $key => $val)
			printf("%-15s %s\n", $key, $val);

		printf("\n");
	}

	public function execute($args)
	{
		if(count($args) == 0 || $args[0] == 'help')
		{
			$this->help();
			die();
		}

		//Check if command even exists
		$method = preg_replace('/-/', '', $args[0]);
		if(!$this->_commandExists($method))
		{
			echo "Error: Subcommand {$args[0]} does not exist.\n";
			echo "Type 'php setup.php {$this->_name} help' for help.\n\n";
			die();
		}

		//call command
		$method = $method . "Command";
		$this->$method(array_slice($args, 1));
	}

	public function getName()
	{
		return $this->_name;
	}

	public function getDescription()
	{
		return $this->_description;
	}

	static protected function _getConfig()
	{
		return self::$_CONFIG;
	}

	protected function _commandExists($cmd)
	{
		$method = $cmd . 'Command';
		return method_exists($this, $method);
	}

	protected function _binaryExists($bin)
	{
		$path = explode(":", $_SERVER['PATH']);
		foreach($path as $p)
		{
			$file = $p . "/" . $bin;
			if(file_exists($file))
				return $file;
		}

		return false;
	}
}

class ProductionCommand extends SetupCommand
{
	private $_app_config;
	private $_revisions;
	private $_rev_file;

	public function __construct()
	{
		$this->_cmds = array('update'      => 'Update to most recent master revision. Also updates any submodules.',
		                     'revert'      => 'Reverts to last revision before an update.',
		                     'revert-list' => 'Show revert list.'); 

		$this->_app_config = Application::getConfig();

		//Setup revision filename
		$this->_rev_file  = "/tmp/{$this->_app_config->application['name']}-revision.log";
		$this->_revisions = ((file_exists($this->_rev_file))? explode("\n", trim(file_get_contents($this->_rev_file))) : array());

		parent::__construct('production', 'Production setup utilities.');
	}

	public function execute($args)
	{
		//TODO: Check if ssh key is in memory

		//check for git existence
		$git = $this->_binaryExists('git');
		if($git === false)
		{
			echo "Unable to find git!!";
			die();
		}

		parent::execute($args);
	}

	public function updateCommand($args)
	{
		$branch = 'master';

		//Get current revision tag
		$revision = `git rev-parse HEAD`;
		$revision = trim($revision);
	
		//Save to revision
		if(!count($this->_revisions) || $this->_revisions[0] != $revision)
			array_unshift($this->_revisions, $revision);

		//Get branch
		$this->_getBranch($branch);

		//save revisions
		$rev = implode("\n", $this->_revisions);
		file_put_contents($this->_rev_file, $rev);
	}

	public function revertCommand($args)
	{
		if(count($this->_revisions) == 0)
		{
			echo "No revisions to revert to.\n";
			die();
		}

		$revision = array_shift($this->_revisions);
		$this->_getBranch($revision, false);

		file_put_contents($this->_rev_file, trim(implode("\n", $this->_revisions)));
	}

	public function revertListCommand($args)
	{
		echo implode("\n", $this->_revisions);
		echo "\n";
	}

	private function _getBranch($branch, $pull=true)
	{
		echo `git checkout $branch`;

		if($pull)
			echo `git pull origin $branch`;

		echo `git submodule update`;

		//Call minifier
		$minifier = new MinifyCommand;
		$minifier->execute(array('all'));
	}
}

class MinifyCommand extends SetupCommand
{
	static private $_COMPRESSOR = "yuicompressor.jar";
	static private $_DCONFIG    = array('javascript' => array('ignore' => array('/.*-min\.js$/'), 'out' => '.js$:-min.js', 'include' => array("/.*\.js$/"), 'combine' => false),
	                                    'stylesheet' => array('ignore' => array('/.*-min\.css$/'), 'out' => '.css$:-min.css', 'include' => array("/.*\.css$/"), 'combine' => false));
	private $_config;

	public function __construct()
	{
		$this->_config = array();
		$this->_cmds   = array('all'        => 'Minify all stylesheet and javascript files.',
		                       'stylesheet' => 'Minify all stylesheet files.',
		                       'javascript' => 'Minify all javascript files.');

		parent::__construct('minify', "Command to minify stylesheets and javascript files. Searches for .setup files in directories for more contextual information.");
	}

	public function execute($args)
	{
		self::$_COMPRESSOR = $this->_binaryExists(self::$_COMPRESSOR);
		if(self::$_COMPRESSOR === false)
		{
			echo "Unable to find compressor '" . self::$_COMPRESSOR . "'. Is it in your path and is it executable (0755)?\n\n";
			die();
		}

		parent::execute($args);
	}

	public function javascriptCommand($args)
	{
		$this->_minify('javascript');
	}

	public function stylesheetCommand($args)
	{
		$this->_minify('stylesheet');
	}

	public function allCommand($args)
	{
		$this->_minify('javascript');
		$this->_minify('stylesheet');
	}

	protected function _checkForCompressor()
	{
	}

	protected function _match($patterns, $subject)
	{
		foreach($patterns as $pattern)
		{
			if(preg_match($pattern, $subject))
				return true;
		}

		return false;
	}

	private function _minify($type)
	{

		$ext     = array('javascript' => 'js', 'stylesheet' => 'css');
		$configs = self::_getConfig();

		//Search all javascript files
		$file  = Application::getConfig()->approotpath . "*." . $ext[$type];
		$files = Application::recursiveGlob($file);

		$dirs = array();
		foreach($files as $file)
		{
			$dir = dirname($file);
			if(!isset($dirs[$dir]))
				$dirs[$dir] = array();

			$dirs[$dir][] = basename($file);
		}

		//Iterate through each directory and apply minify
		foreach($dirs as $key => $vals)
		{
			//Spit out info
			echo "Found " . count($vals) . " files in '" . $key . "'...\n";

			//Check if we have a config
			$config = ((isset($configs[$key]['minify'][$type]))? $configs[$key]['minify'][$type] : self::$_DCONFIG[$type]);

			echo " - Processing '$key'... \r";
			$input = array();
			foreach($vals as $file)
			{
				//Make sure we include files
				if(!$this->_match($config['include'], $file))
					continue;

				//Make sure we don't ignore
				if($this->_match($config['ignore'], $file))
					continue;

				//Add to input list
				$input[] = $file;
			}

			//Setup parameter list
			$cnt   = count($input);
			$out   = $config['out'];
			$ctype = $ext[$type];

			//Call the javascript minifier
			$cwd = getcwd();
			chdir($key);

			$cmd = "java -jar " . self::$_COMPRESSOR . " --type=$ctype ";
			if($config['combine'])
			{
				//Remove out file
				if(file_exists($out))
					unlink($out);

				//Run each time
				foreach($input as $i)
				{
					$run = $cmd . "$i >> $out";
					$ret = `$run`;
				}
			}
			else
			{
				$input = implode(" ", $input);
				$cmd   = "java -jar " . self::$_COMPRESSOR . " --type=$ctype -o $out $input";
				$ret   = `$cmd`;
			}

			chdir($cwd);

			echo " - Processing '$key'... $cnt files processed and " . (count($vals) - $cnt) . " files ignored.\n\n";
		}

	}
}

class SetupScript extends ScriptApplication
{
	static public $DESCRIPTION = "Program to setup the application.";
	private $_cmds;

	public function __construct()
	{
		//Load directory configs
		SetupCommand::loadConfigs();

		$cmds = array('minify', 'production');
		foreach($cmds as $c)
		{
			$class = $c . "Command";
			$this->_cmds[$c] = new $class;
		}
		
		parent::__construct();
	}

	public function run()
	{
		global $argv;
		$argv = array_splice($argv, 2);
		$argc = count($argv);
		if($argc == 0)
		{
			$this->help();
			die();
		}

		if(!isset($this->_cmds[$argv[0]]))
		{
			echo "Unknown command {$argv[0]}.";
			$this->help();
			die();
		}
		
		//Get command
		$cmd = $this->_cmds[$argv[0]];
		
		//execute command
		$cmd->execute(array_slice($argv, 1));
	}

	public function help()
	{
		printf("Help Menu - Commands\n");
		printf("php script.php <command> <sub command>\n");
		printf("--------------------------------------\n");

		foreach($this->_cmds as $cmd)
			printf("%-15s %s\n", $cmd->getName(), $cmd->getDescription());

		printf("\n");
	}
}

//Standalone code in order to directly call this script outside the script.php wrapper
$argv = array_merge(array('script.php', 'setup'), array_slice($argv, 1));
$argc = count($argv);

$script = new SetupScript(array_slice($argv, 2));
require_once('script.php');
?>
