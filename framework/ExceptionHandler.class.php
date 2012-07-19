<?
class ExceptionHandler extends Controller
{
	protected $_view;
	protected $_ex;

	public function errorAction()
	{
		//Clean out buffer
		ob_end_clean();

		//Check if view exists in application land
		if(!$this->doesViewExist('_arbitrage/_error_handler'))
			$this->setViewPath(Application::getConfig()->fwrootpath . 'template/app/views/');

		//Check if layout exists
		if(!$this->doesLayoutExist('_error_handler'))
			$this->setLayoutPath(Application::getConfig()->fwrootpath . 'template/app/layout/');

		return array('render' => '_arbitrage/_error_handler', 'layout' => '_error_handler', 'variables' => array('exceptions' => $this->_walkExceptions($this->_ex)));
	}

	public function setException($ex)
	{
		$this->_ex = $ex;
	}

	private function _walkExceptions($ex)
	{
		$prev = array();
		if($ex->getPrevious() != NULL)
			$prev = $this->_walkExceptions($ex->getPrevious());

		//If EPHPException is there, ensure View render mode
		if($ex instanceof EPHPException)
			$this->setRenderMode("View");

		//Get trace
		$trace = array();
		foreach($ex->getTrace() as $t)
		{
			$entry = array();
			$entry['file']    = $t['file'];
			$entry['line']    = $t['line'];
			$entry['summary'] = $t['function'];

			if(isset($t['class']))
				$entry['summary'] = $t['class'] . $t['type'] . $entry['summary'];

			//Get code 
			$code = explode(PHP_EOL, file_get_contents($t['file']));

			//Grab only 10 lines, with error in center and bolded
			if(count($code) > 0)
			{
				$start = (($t['line'] < 5)? 0 : $t['line']-5);
				$nline = $t['line'] - $start;
				$code  = array_slice($code, $start, 10); //10 lines of code

				$ret = array();
				foreach($code as $key => $c)
				{
					$content = htmlentities((($c == "")? ' ' : $c));
					$content = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $content);
					$ret[] = array('line' => ($t['line'] + $key) - ($nline-1), 'code' => $content, 'selected' => ($key === $nline-1));
				}
				
				$entry['code'] = $ret;
			}

			$trace[] = $entry;
		}

		$exception = array('scope' => $ex->getScope(), 'message' => $ex->getMessage(), 'file' => $ex->getFile(), 'line' => $ex->getLine(), 'trace' => $trace);

		return array_merge($prev, array($exception));
	}
}
?>
