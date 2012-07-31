<?
namespace Framework\Exceptions;

class EArbitrageException extends \Exception
{
	public function __construct($message="", $code=0, $previous=NULL)
	{
		parent::__construct($message, $code, $previous);
	}
}

final class EPHPException extends EArbitrageException
{
	public function __construct($message, $code, $file, $line, $previous=NULL)
	{
		parent::__construct($message, $code, $previous);
		$this->file   = $file;
		$this->line   = $line;
		$this->_scope = "PHP Error";
	}
}

final class EHTTPException extends EArbitrageException
{
	//4xx series
	static public $HTTP_BAD_REQUEST  = 400;
	static public $HTTP_UNAUTHORIZED = 401;
	static public $HTTP_FORBIDDEN    = 403;
	static public $HTTP_NOT_FOUND    = 404;

	//5xx series
	static public $HTTP_INTERNAL_ERROR  = 500;
	static public $HTTP_NOT_IMPLEMENTED = 501;

	public function __construct($code)
	{
		parent::__construct($this->_getMessage($code), $code);
	}

	public function toHeader()
	{
		return "HTTP/1.1 " . $this->getCode() . " " . $this->getMessage();
	}

	public function _getMessage($code)
	{
		switch($code)
		{
			//4xx series
			case self::$HTTP_BAD_REQUEST:
				return "Bad Request";

			case self::$HTTP_UNAUTHORIZED:
				return "Unauthorized";

			case self::$HTTP_FORBIDDEN:
				return "Forbidden";

			case self::$HTTP_NOT_FOUND:
				return "Not Found";

			//5xx series
			case self::$HTTP_INTERNAL_ERROR:
				return "Internal Server Error";

			case self::$HTTP_NOT_IMPLEMENTED:
				return "Not Implemented";

			default:
				return "Unknown Error";
		}
	}
}

final class EArbitrageKernelException extends \Exception { }
final class EArbitrageServiceException extends \Exception { }
final class EArbitrageRenderableException extends \Exception { }
final class EWebApplicationException extends \Exception { }
final class EPHPApplicationException extends \Exception { }
final class EArbitrageConfigException extends EArbitrageException { }
final class EArbitrageRemoteCacheException extends EArbitrageException { }
final class EArrayObjectException extends EArbitrageException { }
final class EDatabaseDriverException extends \Exception { }

?>
