<?php
namespace yimaLocalize\LocaliPlugins\DateTime\Calendar;

use Poirot\Cldr\DataProvider\ProviderAbstract;
use Poirot\DateTime\Calendar\CalendarInterface;
use Poirot\DateTime\CalendarInterval;

abstract class LocalizedAbstract extends ProviderAbstract
    implements CalendarInterface
{
    /**
     * This name is related to CLDR folder structure
     *
     * @var string Name of locale data section
     */
    protected $name = 'common/main';

    /**
     * Name of calendar system
     *
     * @var string
     */
    protected $calName = '';

    /**
     * Get calendar system name
     * exp. gregorian
     *
     * @return string
     */
    public function getName()
    {
        return $this->calName;
    }

    /**
     * English ordinal suffix for the day of the month, 2 characters
     * exp. st, nd, rd or th. Works well with j char format
     * @note return null if there is no specific suffix
     *
     * @return string|null
     */
    abstract public function getMonthSuffix();

    /**
     * Calculate date to calendar system
     *
     * @param int $gYear Year in gregorian system
     * @param int $gMonth Month in gregorian system
     * @param int $gDay Day in gregorian system
     *
     * @return CalendarInterval|false
     */
    abstract public function calculateDate($gYear, $gMonth, $gDay);

    /**
     * Which The day of the year (starting from 0)
     * exp. 0 through 365
     * @note return null mean datetime must use default value
     *
     * @return int|null
     */
    abstract public function calculateDayOfYear($month, $day);

    /**
     * Get array of narrow textual representation of a day
     * sorted by first day of week to end.
     *
     * @param string|int $day (string)Mon through Sun|(int) 1 for Monday through 7
     *
     * @return array|string
     */
    public function getWeekDayNamesNarrow($day = null)
    {
        $calendar = $this->getName();
        // gregorian, generic

        $data = array();
        $calendsFallback = array($calendar, 'gregorian', 'generic');
        foreach ($calendsFallback as $calendar) {
            $data = $this->getRepoReader()->getEntityByPath(
                'dates/calendars/calendar[@type="'.$calendar.'"]/days/dayContext[@type="format"]/dayWidth[@type="abbreviated"]/day'
            );

            if ($data) {
                break;
            }
        }

        $dayOfWeeks = array();
        foreach($data as $d) {
            $dayOfWeeks[$d['type']] = $d['value'];
        }

        $return = ($day == null) ? $dayOfWeeks : false;

        $day = (is_string($day)) ? strtolower($day) : $day;

        if (is_string($day) && isset($dayOfWeeks[$day])) {
            $return = $dayOfWeeks[$day];
        } elseif(is_int($day)) {
            // get with index
            $arrayKeys = array_keys($dayOfWeeks);
            $return    = $dayOfWeeks[$arrayKeys[$day]];
        }

        return $return;
    }

    /**
     * Get array of textual representation of a day
     * sorted by first day of week to end.
     *
     * @param string|int $day (string)Mon through Sun|(int) 1 for Monday through 7
     *
     * @return array|string|false
     */
    public function getWeekDayNames($day = null)
    {
        $calendar = $this->getName();
        // gregorian, generic

        $data = array();
        $calendsFallback = array($calendar, 'gregorian', 'generic');
        foreach ($calendsFallback as $calendar) {
            $data = $this->getRepoReader()->getEntityByPath(
                'dates/calendars/calendar[@type="'.$calendar.'"]/days/dayContext[@type="format"]/dayWidth[@type="wide"]/day'
            );

            if ($data) {
                break;
            }
        }

        $dayOfWeeks = array();
        foreach($data as $d) {
            $dayOfWeeks[$d['type']] = $d['value'];
        }

        $return = ($day == null) ? $dayOfWeeks : false;

        $day = (is_string($day)) ? strtolower($day) : $day;

        if (is_string($day) && isset($dayOfWeeks[$day])) {
            $return = $dayOfWeeks[$day];
        } elseif(is_int($day)) {
            // get with index
            $arrayKeys = array_keys($dayOfWeeks);
            $return    = $dayOfWeeks[$arrayKeys[$day]];
        }

        return $return;
    }

    /**
     * Short textual representation of a month, such as January or March
     *
     * @param int $month
     *
     * @return array|string
     */
    public function getMonthNamesNarrow($month = null)
    {
        $calendar = $this->getName();
        // gregorian, generic

        $data = array();
        $calendsFallback = array($calendar, 'gregorian', 'generic');
        foreach ($calendsFallback as $calendar) {
            $data = $this->getRepoReader()->getEntityByPath(
                'dates/calendars/calendar[@type="'.$calendar.'"]/months/monthContext[@type="format"]/monthWidth[@type="abbreviated"]/month'
            );

            if ($data) {
                break;
            }
        }

        $months = array();
        foreach($data as $d) {
            $months[$d['type']] = $d['value'];
        }

        $return = ($month == null) ? $months : false;

        return isset($months[$month]) ? $months[$month] : $return;
    }

    /**
     * Full textual representation of a month, such as January or March
     *
     * @param int $month
     *
     * @return array|string
     */
    public function getMonthNames($month = null)
    {
        $calendar = $this->getName();
        // gregorian, generic

        $data = array();
        $calendsFallback = array($calendar, 'gregorian', 'generic');
        foreach ($calendsFallback as $calendar) {
            $data = $this->getRepoReader()->getEntityByPath(
                'dates/calendars/calendar[@type="'.$calendar.'"]/months/monthContext[@type="format"]/monthWidth[@type="wide"]/month'
            );

            if ($data) {
                break;
            }
        }

        $months = array();
        foreach($data as $d) {
            $months[$d['type']] = $d['value'];
        }

        $return = ($month == null) ? $months : false;

        return isset($months[$month]) ? $months[$month] : $return;
    }

    /**
     * Get day periods narrow name
     *
     * @param string|null $val am pm
     *
     * @return array|string
     */
    public function getDayPeriodsNarrow($val = null)
    {
        $calendar = $this->getName();
        // gregorian, generic

        $data = array();
        $calendsFallback = array($calendar, 'gregorian', 'generic');
        foreach ($calendsFallback as $calendar) {
            $data = $this->getRepoReader()->getEntityByPath(
                'dates/calendars/calendar[@type="'.$calendar.'"]/dayPeriods/dayPeriodContext[@type="format"]/dayPeriodWidth[@type="narrow"]/dayPeriod'
            );

            if ($data) {
                break;
            }
        }

        $dayperiods = array();
        foreach($data as $d) {
            $dayperiods[$d['type']] = $d['value'];
        }

        $val = strtolower($val);

        return (isset($dayperiods[$val])) ? $dayperiods[$val] : $this->getDayPeriods($val);
    }

    /**
     * Get day periods name
     *
     * @param string|null $val am pm
     *
     * @return array|string
     */
    public function getDayPeriods($val = null)
    {
        $calendar = $this->getName();
        // gregorian, generic

        $data = array();
        $calendsFallback = array($calendar, 'gregorian', 'generic');
        foreach ($calendsFallback as $calendar) {
            $data = $this->getRepoReader()->getEntityByPath(
                'dates/calendars/calendar[@type="'.$calendar.'"]/dayPeriods/dayPeriodContext[@type="format"]/dayPeriodWidth[@type="wide"]/dayPeriod'
            );

            if ($data) {
                break;
            }
        }

        $dayperiods = array();
        foreach($data as $d) {
            $dayperiods[$d['type']] = $d['value'];
        }

        $val = strtolower($val);

        return (isset($dayperiods[$val])) ? $dayperiods[$val] : $this->getDayPeriods($val);
    }
}
