<?php
namespace yimaLocalize\LocaliPlugins;

use Poirot\DateTime;
use yimaLocali\Service\LocaleSupport;
use yimaLocalize\LocaliPlugins\DateTime\Calendar\LocalizedAbstract;
use Zend\ServiceManager;

/**
 * Class AclAuthenticationFactory
 *
 * @package yimaLocalize\LocaliPlugins
 */
class DatetimeFactory implements ServiceManager\FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceManager\ServiceLocatorInterface $serviceLocator
     *
     * @return DateTime
     */
    public function createService(ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        /** @var $serviceLocator \yimaLocali\Service\PluginManager */
        $poirotDatetime = new DateTime();

        /** @var $sm \Zend\ServiceManager\ServiceManager */
        $sm = $serviceLocator->getServiceLocator();

        // Set calendar based on detected locale ---------------------------------------------
        $locale   = $sm->get('locale.detected');
        $data     = LocaleSupport::getLocaleData($locale);
        $calendar = isset($data['calendar']) ? $data['calendar'] : 'gregorian';

        $class = $this->getCalendarClass($calendar);
        if (!class_exists($class, true) && $calendar != 'gregorian') {
            // At least we use localized calendar data
            $calendar = 'gregorian';
            $class = $this->getCalendarClass($calendar);
        }

        $calendar = new $class($locale);
        if (!$calendar instanceof LocalizedAbstract) {
            throw new \Exception('Calendar must instanceof "LocalizedAbstract" but "'.get_class($calendar).'" given.');
        }

        $poirotDatetime->setCalendar($calendar);
        // -----------------------------------------------------------------------------------

        return $poirotDatetime;
    }

    /**
     * Get Calendar class name
     *
     * @param $calendar Calendar name
     *
     * @return string
     */
    protected function getCalendarClass($calendar)
    {
        return __NAMESPACE__.'\DateTime\Calendar\\'.ucfirst(strtolower($calendar));
    }
}
