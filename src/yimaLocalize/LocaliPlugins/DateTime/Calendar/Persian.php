<?php
namespace yimaLocalize\LocaliPlugins\DateTime\Calendar;

use Poirot\DateTime\CalendarInterval;
use Poirot\DateTime\Calendar\Persian as PoirotPersian;

/**
 * Class Persian
 *
 * @package yimaLocalize\LocaliPlugins\DateTime\Calendar
 */
class Persian extends LocalizedAbstract
{
    protected $calName = 'persian';

    /**
     * @var PoirotPersian
     */
    protected $poirotPersian;

    /**
     * Calculate date to calendar system
     *
     * @param int $gYear Year in gregorian system
     * @param int $gMonth Month in gregorian system
     * @param int $gDay Day in gregorian system
     *
     * @return CalendarInterval|false
     */
    public function calculateDate($gYear, $gMonth, $gDay)
    {
        return $this->getPoirotPersian()->calculateDate($gYear, $gMonth, $gDay);
    }

    /**
     * Get Proxy to Poirot Persian Calendar
     *
     * @return PoirotPersian
     */
    protected function getPoirotPersian()
    {
        if (!$this->poirotPersian) {
            $this->poirotPersian = new PoirotPersian();
        }

        return $this->poirotPersian;
    }

}
