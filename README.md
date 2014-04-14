yimaLocalize
============

*this module is part of Yima Application Framework*

[zf2 module] Localization features for multilingual DB`s(translatable fields), CLDR data for full system localization of data, and many predefined useful locale helpers.

Using MultiLingual TableGateway Feature
-----------

#### Setup Database
1) Setup Database adapter inside your application.
2) Create new db in your sql server.
3) Create i18n table with importing yimaLocalize_i18n.sql into your mysql server.
   *you can find this file inside data folder of module*

#### Create your Translatable Table
note: you can register this table as service in serviceManager, later you can invoke this table from SM.

```php
use yimaBase\Db\TableGateway\AbstractTableGateway;
use yimaLocalize\Db\TableGateway\Feature\TranslatableFeature;

class SampleTable extends AbstractTableGateway
{
	# db table name
    protected $table = 'sampletable';

	// this way you speed up running by avoiding metadata call to reach primary key
	// exp. usage in Translation Feature
	protected $primaryKey = 'sampletable_id';

	public function init()
	{
		// this is translatable fields of table
		$feature = new TranslatableFeature(
            array('title', 'description')
        );

		$this->featureSet->addFeature($feature);
	}
}
```
In your controller or somewhere you access ServiceManager, you have this stuff:

1) Add Data In Your Current Locale
    note: when we fetch data from sql only get data inserted from within related locale that insert query was run

```php
$serviceLocator = $this->getServiceLocator();
// get sample table service
$projectTable   = $serviceLocator->get('yimaLocalize.Model.TableGateway.Sample');

$locale         = \Locale::getDefault();
$title = ($locale == 'fa_IR') ? 'عنوان' : 'Title';
$descr = ($locale == 'fa_IR') ? 'توضیحات' : 'Description';

// Translatable Feature will automatically detect locale from \Locale::getDefault()
$projectTable->insert(
    array(
        'title' 	  => $title,
        'description' => $descr,
        'image'       => uniqid().'.jpg',
    )
);

// we can also use update query as well
$projectTable->update(
    array(
        'description' => 'This is '.$locale.' title updated from',
    ),
    array('sampletable_id'=>1 )
);
```

2) Add Translation for data table in other locale language(s)
    note: every time you query for data in desired locale language translation will fetch automatically

```php
$serviceLocator = $this->getServiceLocator();
// get sample table service
$projectTable   = $serviceLocator->get('yimaLocalize.Model.TableGateway.Sample');

$locale         = \Locale::getDefault();
$title = ($locale == 'fa_IR') ? 'عنوان' : 'Title';
$descr = ($locale == 'fa_IR') ? 'توضیحات' : 'Description';

$projectTable->insert(
    array(
        'title' 	  => $title,
        'description' => $descr,
        'image'       => uniqid().'.jpg',
    )
);

$currentLocale = $locale;
$transLocale   = 'fa_IR';

// add translation for other locale ... {
// set locale for translation feature
$projectTable->apply('setLocale', array($transLocale));
// add translation
$projectTable->apply(
    'addTranslationRows',
    array(
        array(
            'title' 	  => $transLocale.' Title',
            'description' => $transLocale.' Description',
        )
    ,$projectTable->getLastInsertValue()
    )
);
// bring back current locale, as you want !!
$projectTable->apply('setLocale', array($currentLocale));
// ... }
```

3) Fetch, Update, Delete From Table

```php
$projectTable->apply('setLocale', array('fa_IR'));

// Select Specific Data For "fa_IR" locale
$select = $projectTable->getSql()->select()
    //->columns(array('t'=>'title','image','url'))
    //->where(array('sampletable_id' => 1))
;
$rowset = $projectTable->selectWith($select);

foreach ($rowset as $projectRow) {
    \Zend\Debug\Debug::dump($projectRow);
}

// Delete Query Will Remove All Data And Translation
$projectTable->delete(
    array('sampletable_id'=>1 )
);
```
