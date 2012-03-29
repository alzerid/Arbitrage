<?
class CHTMLDataTableModel extends CHTMLDataTable
{
	public function __construct($id, $headers, $data, $attrs=array())
	{
		//Convert data to array
		$arr = array();
		foreach($data as $d)
			$arr[] = $d->toArray();

		parent::__construct($id, $headers, $arr, $attrs);
	}
}
?>
