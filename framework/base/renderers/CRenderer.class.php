<?
class CRenderer implements IRenderer
{
	protected $_ctx;

	final public function __construct(IRenderable $ctx)
	{
		$this->_context = $ctx;
	}

	public function getContext()
	{
		return $this->_context;
	}
}
?>
