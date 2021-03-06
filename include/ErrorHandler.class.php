<?
function arbitrage_error_handler_html($errno, $errstr, $errfile, $errline)
{
	$summary = <<<HTML
	<span style="font-size: 20px; color: red;">AN ERROR OCCURRED</span>
	<br />

	<span style="font-size: 12px; color: red;">You have to fix this error before proceeding.</span>
	<table id="errorhandler" name="errorhandler" class='summary'>
		<thead>
			<th colspan="2">Summary</th>
		</thead>
		<tr>
			<td>File:</td>
			<td>$errfile</td>
		</tr>
		<tr>
			<td>Line:</td>
			<td>$errline</td>
		</tr>
		<tr>
			<td>Summary:</td>
			<td>($errno): $errstr</td>
		</tr>
	</table>
HTML;

	$out = array();
	$bt  = debug_backtrace();
	foreach($bt as $b)
	{
		if(!isset($b['class']))
			$b['class'] = "(none)";
		
		$out[] = <<<HTML
		<div>
			<table id="errorhandler" name="errorhandler" class="detail">
				<tr>
					<td>File</td>
					<td>{$b['file']}</td>
				</tr>
				<tr>
					<td>Class</td>
					<td>{$b['class']}</td>
				</tr>
				<tr>
					<td>Function</td>
					<td>{$b['function']}</td>
				</tr>
				<tr>
					<td>Line</td>
					<td>{$b['line']}</td>
				</tr>
			</table>
		</div>
HTML;
	}

	$bt  = '<table id="errorhandler" name="errorhandler" class="stacktrace">';
	$bt .= '<thead><tr><th colspan=\"2\">Stack Trace</th></tr></thead>';
	foreach($out as $o)
	{
		$bt .= "<tr>";
		$bt .= "<td>$o</td>";
		$bt .= "</tr>";
	}
	$bt .= "</table>";

	Application::setBackTrace($summary . $bt);
}

function arbitrage_error_handler_text($errno, $errstr, $errfile, $errline, $vars)
{
	static $errtype = array(
		E_ERROR              => 'Error',
		E_WARNING            => 'Warning',
		E_PARSE              => 'Parsing Error',
		E_NOTICE             => 'Notice',
		E_CORE_ERROR         => 'Core Error',
		E_CORE_WARNING       => 'Core Warning',
		E_COMPILE_ERROR      => 'Compile Error',
		E_COMPILE_WARNING    => 'Compile Warning',
		E_USER_ERROR         => 'User Error',
		E_USER_WARNING       => 'User Warning',
		E_USER_NOTICE        => 'User Notice',
		E_STRICT             => 'Runtime Notice',
		E_RECOVERABLE_ERROR  => 'Catchable Fatal Error');
	
	if(error_reporting() == 0 || !(error_reporting() & $errno))
		return 0;
	
	$errtrace = array(E_ERROR, E_WARNING, E_PARSE, E_NOTICE);
	$date     = date('Y/m/d H:i:s (T)');
	$body     = array();
	
	//Setup body
	$body  = "Date: $date\n";
	$body .= "Type: {$errtype[$errno]}\n";
	$body .= "File: $errfile\n";
	$body .= "Line: $errline\n";
	$body .= "Message: $errstr\n";

	//Add vars
	if($errno == E_ERROR)
		$body .= "Variables: " . var_export($vars, true) . "\n";

	$body .= "Trace:\n";

	//Backtrace
	$trace = debug_backtrace();
	array_shift($trace);
	foreach($trace as $idx=>$t)
		$body .= "[$idx] {$t['function']} - {$t['file']}:{$t['line']}\n";
	
	$display = ini_get('display_errors');
	if($display != 1)
	{
		//Log the error
		error_log($body);

		//Email error
		if($errno == E_ERROR)
			error_log($body, 1, 'eric@dualclutchmedia.com');
	}
	else
	{
		if(php_sapi_name() == 'cli')
			echo $body;
		else
			echo '<div class="php_error_entry">' . nl2br($body) . '</div>';
	}
}

$config  = Application::getConfig();
$handler = ((isset($config->arbitrage['error_handler']))? $config->arbitrage['error_handler'] : "");

switch($handler)
{
	case 'html':
		set_error_handler('arbitrage_error_handler_html');
		break;
	
	case 'text':
		set_error_handler('arbitrage_error_handler_text');
		break;
}
?>
