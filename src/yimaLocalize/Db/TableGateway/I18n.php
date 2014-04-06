<?php
namespace yimaLocalize\Db\TableGateway;

use YimaBase\Db\TableGateway\AbstractTableGateway;

/**
 * Translation tables must instace of \Zend\Db\Adapter\AdapterAwareInterface
 * or Extended Application\Db\TableGateway\AbstractTableGateway
 * to have adapter database available to work.
 * 
 */
class I18n extends AbstractTableGateway
{
	# db table name
    protected $table = 'yimaLocalize_i18n';

}