<?
class EArbitrageException extends Exception
{
	public function __construct($message="", $code=0, $previous=NULL)
	{
		parent::__construct($message, $code, $previous);
	}
}

final class PHPException extends EArbitrageException
{
	public function __construct($message, $code, $file, $line, $previous=NULL)
	{
		parent::__construct($message, $code, $previous);
		$this->file   = $file;
		$this->line   = $line;
		$this->_scope = "PHP Error";
	}
}

final class EArbitrageConfigException extends EArbitrageException
{
}

final class EArbitrageRemoteCacheException extends EArbitrageException
{
}
?>
