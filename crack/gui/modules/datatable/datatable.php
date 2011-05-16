<?
class DataTableModule extends Module
{
	static public $_NUMBER = -1;

	public function __construct($application, $options)
	{
		parent::__construct('datatable', $application, $options);
		$this->includeJavascript('datatable.js');
		self::$_NUMBER++;

		//Check if options is true, if so use edit/remove/clone option
		if(isset($this->_options['options']) && $this->_options['options'] === true)
			$this->_options['options'] = "erc";

	}

	public function setOptions($opts)
	{
		parent::setOptions($opts);
		
		//Default options
		$default_options = array(
			'e' => function($data) {
				return HTMLComponent::generateLink('<img src="/modules/datatable/public/images/edit_16x16.png" alt="Edit" title="Edit" />', array('edit', array('id' => $data->getID())), array('name' => "options_edit_{$data->getID()}", 'id' => "options_edit_{$data->getID()}"));
			},
	    'r' => function($data) { 
				return HTMLComponent::generateLink('<img src="/modules/datatable/public/images/delete_16x16.png" alt="Delete" title="Delete" />', '#', array('onclick' => 'return controller.bulkDelete(this);', 'name' => "options_remove_{$data->getId()}", 'id' => "options_remove_{$data->getId()}"));
			},
			'c' => function($data) {
				return HTMLComponent::generateLink('<img src="/modules/datatable/public/images/clone_16x16.png" alt="Clone" title="Clone" />', array('clone', array('id' => $data->getId())), array('name' => "options_clone_{$data->getId()}", 'id' => "options_clone_{$data->getId()}"));
			}
		);

		//Merge default options into 
		if(!isset($this->_options['options_extra']))
			$this->_options['options_extra'] = $default_options;
		else
			$this->_options['options_extra'] = array_merge($default_options, $this->_options['options_extra']);

		//Add to js_options
		if(isset($this->_options['js_options']) && isset($this->_options['options']))
			$this->_options['js_option']['options'] = $this->_options['options'];
	}

	public function process()
	{
		return '';
	}

	protected function _getName()
	{
		return "datatable_" . self::$_NUMBER;
	}

	protected function _getJSOptions()
	{
		$funcs  = array('onRender', 'onLoad'); //Strings that should be converted to functions because json_encode doesnt do it for us --EMJ
		$jsopts = ((isset($this->_options['js_options']))? $this->_options['js_options'] : array());

		//Check to see if options field is set
		if(isset($this->_options['options']) && $this->_options['options'])
			$jsopts['options'] = $this->_options['options'];

		$json = json_encode($jsopts); 

		//Find render_cb and make it a function (non greedy)
		foreach($funcs as $func)
			$json = preg_replace('/"' . $func . '":"(.*)",/U', '"' . $func . '":\1,', $json);

		return $json;
	}

	protected function _getHeaders()
	{
		$columns = ((isset($this->_options['columns']))? $this->_options['columns'] : NULL);
		$labels  = ((isset($this->_options['labels']))? $this->_options['labels'] : NULL);
		$data    = ((isset($this->_options['data'][0]))? $this->_options['data'][0] : array());
		$headers = array();

		//Check if bulk actions are set
		if(isset($this->_options['bulk_actions']) && $this->_options['bulk_actions'])
			$headers[] = '';

		if($columns == NULL)
			return $headers;

		foreach($columns as $column)
		{
			if(isset($labels[$column]))
				$headers[$column] = $labels[$column];
			else
				$headers[$column] = $column;
		}
		
		//Check if bulk actions are set
		if(isset($this->_options['options']) && $this->_options['options'])
			$headers[] = 'Options';

		return $headers;
	}

	protected function _getData()
	{
		$table = array();
		foreach($this->_options['data'] as $data)
		{
			$row = array();
			foreach($this->_options['columns'] as $column)
			{
				$cell = '';
				if($column == '')
					$cell = "";
				elseif(is_subclass_of($data, 'Model'))
					$cell = $data->getDotNotationValue($column);
				else
					$cell = $data->$column;

				//Check if we need to normalize
				if(isset($this->_options['normalize_data']) && isset($this->_options['normalize_data'][$column]))
				{
					$normalize = $this->_options['normalize_data'][$column];
					if(is_string($normalize))
						eval('$cell=' . $normalize);
					elseif(is_callable($normalize))
						$cell = $normalize($cell);
				}
				
				$row[] = $cell;
			}

			//Add id
			$id = $data->getID();
			$row['id'] = $id;

			//Add options
			if(isset($this->_options['options']))
			{
				$html    = "";
				$options = $this->_options['options'];
				$extra   = $this->_options['options_extra'];
				for($i=0; $i<strlen($options); $i++)
				{
					$key  = $options[$i];
					$func = ((isset($extra[$key]))? $extra[$key] : NULL);

					if($func)
						$html .= $func($data) . " ";
					else
						$html .= "Unknown table options '{$key}'. ";
				}

				//Add options to the row
				$row[] = $html;
			}

			//Add the row to the table
			if(count($row))
				$table[] = $row;
		}

		return $table;
	}
}
?>
