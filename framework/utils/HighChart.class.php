<?
class HighChart
{
	public $chart;
	public $title;

	public $yAxis;
	public $xAxis;

	public $series;
	public $credits;
	
	public function __construct($renderTo, $defaultSeriesType, $title = "")
	{
		$this->chart             = array("renderTo" => $renderTo, "defaultSeriesType" => $defaultSeriesType);
		$this->title             = array("text" => $title);
		$this->renderTo          = $renderTo;
		$this->defaultSeriesType = $defaultSeriesType;
		$this->series            = array();
		$this->credits           = array("enabled" => false);
	}

	public function setTitle($title)
	{
		$this->title = array('text' => $title);
	}

	public function yAxis($arr)
	{
		$this->yAxis = $arr;
	}

	public function xAxis($arr)
	{
		$this->xAxis = $arr;
	}

	public function addSeries($arr)
	{
		$this->series[] = $arr;
	}

	public function jsonify()
	{
		return json_encode($this);
	}
}
?>
