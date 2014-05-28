<?php
namespace yimaLocalize\LocaliPlugins\DateTime\Calendar;
use Poirot\DateTime\CalendarInterval;

/**
 * Class Gregorian
 *
 * @package yimaLocalize\LocaliPlugins\DateTime\Calendar
 */
class Gregorian extends LocalizedAbstract
{
    protected $calName = 'gregorian';

    /**
     * English ordinal suffix for the day of the month, 2 characters
     * exp. st, nd, rd or th. Works well with j char format
     * @note return null if there is no specific suffix
     *
     * @return string|null
     */
    public function getMonthSuffix()
    {
        return null;
    }

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
        return false;
    }

    /**
     * Number of days in the given month
     *
     * @param int $month Month
     * @param int $year Year
     *
     * @return int
     */
    public function getNumberOfDaysInMonth($month, $year)
    {
        return null;
    }

    /**
     * Which The day of the year (starting from 0)
     * exp. 0 through 365
     * @note return null mean datetime must use default value
     *
     * @return int|null
     */
    public function calculateDayOfYear($month, $day)
    {
        return null;
    }
}
