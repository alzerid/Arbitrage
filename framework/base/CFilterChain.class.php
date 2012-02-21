<?
class CFilterChain
{
	private $_filters;
	private $_controller;

	public function __construct(CBaseController $controller)
	{
		$this->_controller = $controller;
		$this->_filters    = $this->_controller->filters();
	}

	public function runBeforeFilterChain()
	{
		if(!array_key_exists('before_filter', $this->_filters))
			return;

		$filters = $this->_filters['before_filter'];
		foreach($filters as $filter)
		{
			//Filter is a method within the controller
			if(is_string($filter))
				$this->_controller->$filter();
			elseif(is_object($filter) && get_parent_class($filter) === "IFilter")
				$filter->execute();
		}
	}

	public function runAfterFilterChain()
	{
		if(!array_key_exists('after_filter', $this->_filters))
			return;

		$filters = $this->_filters['after_filter'];
		foreach($filters as $filter)
		{
			//Filter is a method within the controller
			if(is_string($filter))
				$this->_controller->$filter();
			elseif(is_object($filter) && get_parent_class($filter) === "IFilter")
				$filter->execute();
		}
	}

	public function runPostProcess($content)
	{
		if(!array_key_exists('post_process', $this->_filters))
			return $content;

		$filters = $this->_filters['post_process'];
		foreach($filters as $filter)
			$content = $this->_controller->$filter($content);

		return $content;
	}
}
?>
