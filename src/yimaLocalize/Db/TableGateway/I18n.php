<?php
namespace yimaLocalize\Db\TableGateway;

use yimaBase\Db\TableGateway\AbstractTableGateway;

/**
 * Translation tables must instace of \Zend\Db\Adapter\AdapterAwareInterface
 * or Extended Application\Db\TableGateway\AbstractTableGateway
 * to have adapter database available to work.
 * 
 */
class I18n extends AbstractTableGateway
{
	# db table name
    protected $table = 'yimalocalize_i18n';

}