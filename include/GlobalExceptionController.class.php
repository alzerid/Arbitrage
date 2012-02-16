<?
class GlobalExceptionController extends Controller
{
	protected $_view;
	protected $_ex;

	public function errorAction()
	{
		//Clean out buffer
		ob_end_clean();

		//Set view path
		$this->setViewPath(Application::getConfig()->fwrootpath . 'template/views/');

		//Setup 
		$trace = array();
		$ex    = $this->_ex;
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
				$start = (($t['line'] <= 6)? 0 : $t['line']-5);
				$nline = $t['line'] - $start;
				$code  = array_slice($code, $start, 10); //10 lines of code

				$ret = array();
				foreach($code as $key => $c)
					$ret[] = array('line' => ($t['line'] + $key) - 6, 'code' => $c, 'selected' => ($key === $nline-1));
				
				$entry['code'] = $ret;
			}

			$trace[] = $entry;
		}

		return array('render' => '_global/error', 'layout' => 'error', 'variables' => array('scope' => $ex->getScope(), 'message' => $ex->getMessage(), 'file' => $ex->getFile(), 'line' => $ex->getLine(), 'trace' => $trace));
	}

	public function setException($ex)
	{
		$this->_ex = $ex;
	}
}
?>
