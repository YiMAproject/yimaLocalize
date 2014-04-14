<?php
namespace yimaLocalize\Model\TableGateway;

use yimaBase\Db\TableGateway\AbstractTableGateway;
use yimaLocalize\Db\TableGateway\Feature\TranslatableFeature;

/**
 * Class Sample
 *
 * @package yimaLocalize\Model\TableGateway
 */
class Sample extends AbstractTableGateway 
{
	# db table name
    protected $table = 'sampletable';

	// this way you speed up running by avoiding metadata call to reach primary key
	// exp. usage in Translation Feature
	protected $primaryKey = 'sampletable_id'; 

	public function init()
	{
		$feature = new TranslatableFeature(
            array('title', 'description')
        );

		$this->featureSet->addFeature($feature);
	}
}
