<?

function arbitrage_error_handler($errno, $errstr, $errfile, $errline)
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


set_error_handler('arbitrage_error_handler');
?>
