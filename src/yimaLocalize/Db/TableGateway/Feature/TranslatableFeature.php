<?php
/**
 * This class writen as a feature based on simple sql query for translation, described below:
 * 
   	SELECT 		`sampletable`.*, `I18n__title`.`content` AS `title`, `I18n__description`.`content` AS `description` 
     FROM   	`sampletable` 
    INNER JOIN  `cLocali_i18n` AS `I18n__title` 
     ON 		I18n__title.foreign_key = `sampletable`.`sampletable_id`
     			AND I18n__title.model   = 'cApplication\\Model\\TableGateway\\Sample'
  				AND I18n__title.field   = 'title'
  				AND I18n__title.locale  = 'en_US'
    INNER JOIN  `cLocali_i18n` AS `I18n__description` 
     ON			I18n__description.foreign_key = `sampletable`.`sampletable_id`
     			AND I18n__description.model   = 'cApplication\\Model\\TableGateway\\Sample'
   				AND I18n__description.field   = 'description'
  				AND I18n__description.locale  = 'en_US'   
 */

namespace yimaLocalize\Db\TableGateway\Feature;

use Locale as StdLocale;
use Traversable;

use yimaBase\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\Feature\AbstractFeature;
use Zend\Db\TableGateway\Exception;

use yimaLocalize\Db\TableGateway\I18n;
use Zend\Stdlib\ArrayUtils;

use Zend\Db\Metadata\Metadata;
use Zend\Db\Sql\Expression;

use yimaBase\Db\TableGateway\Provider\PrimaryKeyProviderInterface;

/**
 * Class TranslatableFeature
 *
 * @package yimaLocalize\Db\TableGateway\Feature
 */
class TranslatableFeature extends AbstractFeature
{
	/**
	 * Locale 
	 *
	 * @var string
	 */
	protected $locale;
	
	/**
	 * Static Default Locale
	 * 
	 * @var string
	 */
	protected static $staticLocale;
	
	/**
	 * Columns of table to be localized
	 *
	 * @var array
	 */
	protected $translatableFields = array();
	
	/**
	 * Translation TableGateway Instance
     * : this table store translation rows
	 *
	 * @var AbstractTableGateway
	 */
	protected $i18nTable;
	
	/**
	 * @see preInsert
	 * 
	 * @var array
	 */
	protected $storedValues;

    /**
     * Construct
     *
     * @param array                     $translatableFields Translatable Fields
     * @param null|AbstractTableGateway $i18nTable          Table for Translation Rows
     * @param null|string               $locale             Locale
     */
    public function __construct($translatableFields = array() , $i18nTable = null, $locale = null)
	{
		if (! empty($translatableFields) ) {
			$this->setTranslatableFields($translatableFields);
		}
		
		if ($i18nTable !== null) {
            $this->setTranslationTable($i18nTable);
		}
		
		if ($locale !== null) {
			$this->setLocale($locale);
		}
	}

    /**
     * Set Locale
     *
     * @param string $locale Locale
     *
     * @return $this
     */
    public function setLocale($locale)
	{
		$this->locale = (string) $locale;

		return $this;
	}
	
	/**
	 * Get Current locale
	 *
	 * @return string
	 */
	public function getLocale()
	{
		if (!$this->locale) {
            $locale = self::getStaticLocale();
            if (!$locale) {
                $locale = StdLocale::getDefault();
            }

            $this->setLocale($locale);
		}
		
		return $this->locale;
	}
	
	/**
	 * Set Translation TableGateway
	 *
	 * @param AbstractTableGateway $tableGateway TableGateway Instance
     *
     * @return $this
	 */
	public function setTranslationTable(AbstractTableGateway $tableGateway)
	{
		// If Translation table dont have adapter yet
		if (! $tableGateway->getAdapter()
            && $tableGateway instanceof \Zend\Db\Adapter\AdapterAwareInterface
        ) {
			$tableGateway->setAdapter($this->tableGateway->adapter); 
		}
		
		$this->i18nTable = $tableGateway;

		return $this;
	}

    /**
     * Get Translation TableGateway Instance
     *
     * @return AbstractTableGateway
     */
    public function getTranslationTable()
	{
        if (!$this->i18nTable) {
            $this->setTranslationTable(new I18n());
        }

		return $this->i18nTable;
	}

	/**
	 * Set Translatable Columns
	 *
	 * @param array $fields Translatable Columns
     *
     * @return $this
	 */
	public function setTranslatableFields(array $fields)
	{
		$this->translatableFields = $fields;

		return $this;
	}

    /**
     * Get Translatable Columns
     *
     * @return array
     */
    public function getTranslatableFields()
    {
        return $this->translatableFields;
    }
	
	/**
	 * Add Translation Field(s) to current field(s)
	 *
	 * @param array $field Translatable Column(s)
     *
     * @return $this
	 */
	public function addTranslatableField(array $field)
	{
		$currFields = $this->getTranslatableFields();

		$this->setTranslatableFields(array_merge($currFields, $field));
	
		return $this;
	}
	
	/**
	 * Set static locale
	 *
	 * @param string $locale
	 */
	public static function setStaticLocale($locale)
	{
		static::$staticLocale = (string) $locale;
	}
	
	/**
	 * Get static locale
	 *
	 * @return string
	 */
	public static function getStaticLocale()
	{
		return static::$staticLocale;
	}
	
	// ............................................................................................................
	
	public function preSelect($select)
	{
		$tableGateway = $this->tableGateway;
		$tableName    = $tableGateway->getTable();
		$tableClass   = get_class($tableGateway);

		$tablePrimKey = $this->getPrimaryKey($tableGateway);    
		
		$locale       = $this->getLocale();
		
		foreach ($this->getTranslatableFields() as $tf) {
			$name = 'I18n__'.$tf;
			// get name of translation table
			$joinTable = $this->getTranslationTable()->table;
			
			$expression = new Expression("
				$name.foreign_key = ?.?
				AND $name.model = ?
				AND $name.field = ?
				AND $name.locale = ?"
				,array(
					$tableName,$tablePrimKey,
					$tableClass,
					$tf,
					$locale
				)
				,array(
					Expression::TYPE_IDENTIFIER,
					Expression::TYPE_IDENTIFIER
				)
			);
			
			$select->join(
				array($name => $joinTable),//join table name
				$expression //conditions
				// `I18n__description`.`content` AS `description`
				#,array($tf => 'content') 
					// this way we dont need postSelect replacement
			
				// `I18n__description`.`content` AS `sampletableI18n__description`
				,array($tableName.$name => 'content') 
					// toSolve: dar taghaabol baa exp. DMS gharaar migirad va field e tarjome shode tarjome nemishavad
			);
		}
	}
	
	public function postSelect($statement, $result, $resultSet)
	{
		$return = $resultSet->toArray();
		
		$tableName = $this->tableGateway->getTable();
		foreach ($return as $i => $row) {
			foreach ($row as $field => $val) {
				$name = $tableName.'I18n__';
				// agar field e translate bood baa meghdaar e field e asli jaaigozin mishavad
				if (strpos($field, $name) !== false) {
					$originField = substr($field, strlen($name), (strlen($field)-strlen($name)));
					$return[$i][$originField] = $val;
					unset($return[$i][$field]);
				}
			}
		}
		
		$resultSet->initialize($return);
	}
	
	public function preInsert($insert)
	{
		// reset on insert values
		$this->setStoredValues();
		
		/* value haaie ersaal shode ro mibinim agar haavie
		 * ettela'aate translation bood aanhaa ro kenaar migzaarim
		 * va dar postInsert aanhaa raa be table translate ezaafe mikonim 
		 */
		$rawData = $insert->getRawState();
		$columns = $rawData['columns'];
		$values  = $rawData['values'];
		
		$trnsColumns = $this->getTranslatableFields();
		$storedVal   = array();// translatable column must insert on postInsert
		foreach ($columns as $key=>$cl) {
			if (in_array($cl,$trnsColumns)) {
				$storedVal[$cl] = $values[$key];
			}
		}
		
		$this->setStoredValues($storedVal);
	}
	
	public function postInsert($statement, $result)
	{
		// get insert statement column => value pair
		// $InsertedValues 	 = $statement->getParameterContainer()->getNamedArray();
		
		$TranslatableColumns = $this->getStoredValues();
		if (empty($TranslatableColumns)) {
			// we dont have any translatable columns
			return;
		}
		
		// for Inserting into translation table we need ID of last inserted row
		$lastID = $result->getGeneratedValue();
		
		$this->addTranslationRows($TranslatableColumns,$lastID);
	}
	
	public function preUpdate($update)
	{
		// reset stored values
		$this->setStoredValues();
		
		// dar ebtedaa field haaii ke marboot be translatable haa nistand dar jadval asli update shavad
		$rawState = $update->getRawState();
		$dataset  = $rawState['set'];
		
		$noTransdata = array_diff_key($dataset, array_flip($this->getTranslatableFields()));
		
		// store translatableData
		$storedData           = $this->getTranslatableDataFrom($dataset);
		$storedData['@where'] = clone $update->where;
		
		if (empty($noTransdata)) {
			// we don't want change anything in base table
			// store where part and change it to nothing happend.
			$update->where(array('1 = ?' => 0));
		} else {
			$update->set($noTransdata);
		}
		
		$this->setStoredValues($storedData);
	}

	public function postUpdate($statement, $result)
	{
		$storedValues = $this->storedValues;
		
		$where = $storedValues['@where'];
		unset($storedValues['@where']);
		
		if (empty($storedValues)) {
			// we dont have any translatabale field
			return;
		}
		
		$tableGateway = $this->tableGateway;
		
		# we dont want use tableGateway baraaie inke feature haa raa niaaz nadaarim
		# be alave inke hamin feature rooie table hatman hast va az select e in estefaade mikonim, kaahesh performance
		$sql       = $tableGateway->getSql();
		$select    = $sql->select()->where($where);
		$statement = $sql->prepareStatementForSqlObject($select);
		$rows      = $statement->execute();
		
		if (! count($rows) > 0) {
			return;
		}
		
		$tTable = $this->getTranslationTable();
		// get query data
		$primaryKey = $this->getPrimaryKey($tableGateway);
		$locale     = $this->getLocale();
		$model      = get_class($tableGateway);
		
		// @TODO $result Affected ro raa az PDOstatement migirad va nemishavad aan raa ta'ghir daad
		
		$affectedRows = 0;
		foreach ($rows as $row) {
			$foreignKey = $row[$primaryKey];
			foreach ($storedValues as $column => $val) {
				$r = $tTable ->update(array('content' => $val),array(
					'locale = ?' 	  => $locale,
					'foreign_key = ?' => $foreignKey,
					'field = ?' 	  => $column,
					'model = ?' 	  => $model,
				));
				
				$affectedRows = ($r) ? $affectedRows+$r : $affectedRows; 
			}
		}
	}
	
	public function preDelete($delete)
	{
		$tableGateway = $this->tableGateway;
		$prKey = $this->getPrimaryKey($tableGateway);
		
		// baraaie hazf kadane translation haa yek baar baayad select konim va sepas 
		// az rooie ID(primary key) be dast aamade az jadvale translation foreinKey haaye
		// moshaabeh ro hazf konim
		$where = $delete->where;
		# we dont want use tableGateway baraaie inke feature haa raa niaaz nadaarim
		# be alave inke hamin feature rooie table hatman hast va az select e in estefaade mikonim, kaahesh performance
		$sql       = $tableGateway->getSql();
		$select    = $sql->select()->where($where);
		$statement = $sql->prepareStatementForSqlObject($select);
		$rows      = $statement->execute();
		
		if (! count($rows) > 0) {
			return;
		}
		 
		// primary key of must deleted item
		$ids  = array();
		foreach ($rows as $row) {
			$ids[] = $row[$prKey];
		}
		
		// delete from translation table
		$modelColumn = get_class($tableGateway);
		// "foreign_key IN(?) AND model = $modelColumn"
		$this->getTranslationTable()->delete(array(
			'foreign_key'   => $ids,
			'model = ?'     => $modelColumn
		));
	}
	
	// ............................................................................................................
	
	/**
	 * @see preInsert
	 * 
	 * @param array $values
	 */
	protected function setStoredValues($values = array()) 
	{
		$this->storedValues = $values;
	}
	
	protected function getStoredValues()
	{
		return $this->storedValues;
	}
	
	public function addTranslationRows($rows, $foreignID)
	{
		foreach ($rows as $column => $value) {
			$this->addTranslationRow($column,$value,$foreignID);
		}
	}
	
	/**
	 * Add translation data for a row with specific primary key
	 *
	 * @param int   $pk   | primary key of row
	 * @param array $data | data baayad haavie tamaami e translation field haa baashad,
	 * 						dar gheire in soorat dar select yaaft nemishavad
	 */
	public function addTranslationRow($column, $value, $foreignID)
	{
		$tableGateway = $this->tableGateway;
		
		// write it
		$trData = array(
			'model' 	  => get_class($tableGateway),
			'foreign_key' => $foreignID,
			'field' 	  => $column,
			'locale' 	  => $this->getLocale(),
			'content'	  => $value
		);
		
		// insert to translation table
		$transTable = $this->getTranslationTable();
		$transTable->insert($trData);
	}
	
	/**
	 * Az array aanhaaee raa ke marboot be translatable fields hast raaa
	 * bar migardaanad
	 *
	 * @param array $data
	 */
	protected function getTranslatableDataFrom(array $data)
	{
		return array_intersect_key($data, array_flip($this->getTranslatableFields()));
	}
	
	protected function getPrimaryKey($tableGateway)
	{
		if ($tableGateway instanceof PrimaryKeyProviderInterface) {
			$primaryKey = $tableGateway->getPrimaryKey(); 
		}
		if ($primaryKey) {
			return $primaryKey;
		}

		// try to catch primary key from metada ...................................................................
		
		$metadata = new Metadata($tableGateway->adapter);
		
		// localize variable for brevity
		$t = $tableGateway;
		
		// process primary key
		$pkc = null;
		foreach ($metadata->getConstraints($tableGateway->table) as $constraint) {
			/** @var $constraint \Zend\Db\Metadata\Object\ConstraintObject */
			if ($constraint->getType() == 'PRIMARY KEY') {
				$pkc = $constraint;
				break;
			}
		}
		
		if ($pkc === null) {
			throw new Exception\RuntimeException('A primary key for this column could not be found in the metadata.');
		}
		
		if (count($pkc->getColumns()) == 1) {
			$pkck = $pkc->getColumns();
			$primaryKey = $pkck[0];
		} else {
			$primaryKey = $pkc->getColumns();
		}
		
		return $primaryKey;
	}
	
}
