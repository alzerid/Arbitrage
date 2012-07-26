<?
namespace Framework\Base;
use \Arbitrage2\Base\Controller;

class CFilterChain
{
	private $_filters;
	private $_controller;
	private $_propagate;

	public function __construct(CController $controller)
	{
		$this->_controller = $controller;
		$this->_filters    = $this->_controller->filters();
		$this->_propagate  = true;
	}

	/**
	 * Method runs the filter based on filter_type.
	 * @param string $filter_type The filter type to execute.
	 * @param array $args The argument array list to pass to the filter method/object.
	 */
	public function runFilter($filter_type, &$args=NULL)
	{
		if(!array_key_exists($filter_type, $this->_filters))
			return;

		//Execute the filters
		$filters = $this->_filters[$filter_type];
		foreach($filters as $filter)
		{
			if(!$this->_propagate)
				break;

			if(is_string($filter))
				$this->_controller->$filter($args);
			elseif(is_object($filter) && $filter instanceof IFilter)
				$filter->execute($args);
		}

		//Reset propagate
		$this->_propagate = true;
	}

	/**
	 * Method stops the filter chain propagation.
	 */
	public function stopPropagation()
	{
		$this->_propagate = false;
	}
}
?>
