<?
namespace Framework\Model\DataTypes;

class CDateDataType extends \DateTime implements \Framework\Interfaces\IModelDataType
{
	/**
	 * Method instantiates the CDateDataType.
	 * @param $data The variables to set as default data for this Model.
	 */
	public function __construct($date=NULL, $tz=NULL)
	{
		parent::__construct();
		$this->setValue($date, $tz);
	}

	/**
	 * Method sets the value of this DataType.
	 * @param $date The date to set.
	 * @param $tz The timezone to set.
	 */
	public function setValue($date=NULL, $tz=NULL)
	{
		//Set timezone
		$tz = new \DateTimeZone((($tz!==NULL)? $tz : date_default_timezone_get()));

		//Set date
		if(is_numeric($date))
			$this->setTimestamp($date);
		elseif(is_string($date))
			$this->setTimestamp(strtotime($date));
		elseif($date instanceof \DateTime)
			$this->setTimestamp($date->getTimestamp());
		elseif($date == NULL)
			$this->setTimestamp(time());
		else
			throw new \Framework\Exceptions\EModelDataTypeException("Unable to handle data conversion '$date'.");

		//Set time zone
		$this->setTimezone($tz);
	}

	/**
	 * Method returns this.
	 */
	public function getValue()
	{
		return $this;
	}

	/**
	 * To string
	 */
	public function __toString()
	{
		return $this->format("r");
	}
}
?>
