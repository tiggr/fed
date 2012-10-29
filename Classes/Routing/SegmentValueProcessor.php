<?php
class Tx_Fed_Routing_SegmentValueProcessor {

	const CONVERT_MODEL = 'Model';
	const CONVERT_DATETIME = 'DateTime';
	const CONVERT_NULL = 'Nullify';

	const ENCODE_STRING_STANDARD = 'StandardStringPart';

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
	}

	/**
	 * Translates one segment value (if this class is set as UserFunc).
	 *
	 * @param $params
	 * @param tx_realurl $reference
	 * @return mixed
	 */
	public function translateSegmentValue(&$params, tx_realurl &$reference) {
		$value = $params['value'];
		if (empty($value) === TRUE) {
			return NULL;
		}
		$parameters = $params['setup']['parameters'];
		$direction = isset($params['origValue']) ? 'decode' : 'encode';
		$conversionMethodName = $direction . $parameters['conversionMethod'];
		if (method_exists($this, $conversionMethodName) === FALSE) {
			throw new Tx_Fed_Routing_RoutingException('Invalid conversion method: ' . $parameters['conversionMethod']);
		}
		return call_user_func_array(array($this, $conversionMethodName), array($value, $parameters));
	}

	/**
	 * @param string $string
	 * @param array $parameters
	 * @return string
	 */
	protected function encodeStandardStringPart($string, $parameters) {
		$stringConvertPattern = isset($parameters['stringConvertPattern']) ? $parameters['stringConvertPattern'] : '/([^a-z0-9\-]{1,})+/i';
		$stringConvertReplacement = isset($parameters['stringConvertReplacement']) ? $parameters['stringConvertReplacement'] : '-';
		$string = preg_replace($stringConvertPattern, $stringConvertReplacement, $string);
		$string = strtolower($string);
		$string = trim($string, '-');
		return $string;
	}

	/**
	 * @param integer $uid
	 * @param array $parameters
	 * @mixed
	 */
	protected function encodeModel($uid, $parameters) {
		$uid = intval($uid);
		$tableName = $parameters['tableName'];
		$className = $parameters['className'];
		$labelField = $GLOBALS['TCA'][$tableName]['ctrl']['label'];
		$repositoryClassName = str_replace('_Domain_Model_', '_Domain_Repository_', $className) . 'Repository';
		if (class_exists($repositoryClassName) === FALSE) {
			throw new Tx_Fed_Routing_RoutingException('Class ' . $className . ' does not appear to have a Repository; this is required. '
				. 'The tried Repository class name was ' . $repositoryClassName, 1351541118);
		}
		/** @var $repository Tx_Extbase_Persistence_Repository */
		$repository = $this->objectManager->get($repositoryClassName);
		$query = $repository->createQuery();
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->matching($query->equals('uid', $uid));
		$object = $query->execute()->getFirst();
		if (!$object) {
			throw new Tx_Fed_Routing_RoutingException('Unable to fetch ' . $className . ':' . $uid . ' from ' . $repositoryClassName, 1351541402);
		}
		$propertyName = t3lib_div::underscoredToLowerCamelCase($labelField);
		$propertyValue = Tx_Extbase_Reflection_ObjectAccess::getProperty($object, $propertyName);
		$stringConversionMethod = isset($parameters['stringEncodeMethod']) ? $parameters['stringEncodeMethod'] : self::ENCODE_STRING_STANDARD;
		$stringConversionMethodName = 'encode' . $stringConversionMethod;
		if (method_exists($this, $stringConversionMethodName) === FALSE) {
			throw new Tx_Fed_Routing_RoutingException('String conversion method "' . $stringConversionMethod . '" is invalid', 1351541779);
		}
		$convertedPropertyValue = call_user_func_array(array($this, $stringConversionMethodName), array($propertyValue, $parameters));
		$this->setModelUidAliasCache($uid, $convertedPropertyValue, $tableName, $labelField);
		return $convertedPropertyValue;
	}

	/**
	 * @param mixed $identity
	 * @param array $parameters
	 * @return mixed
	 */
	protected function decodeModel($identity, $parameters) {
		$tableName = $parameters['tableName'];
		$labelField = $GLOBALS['TCA'][$tableName]['ctrl']['label'];
		return $this->getModelUidFromAliasCache($identity, $tableName, $labelField);
	}

	/**
	 * @param string $alias
	 * @param string $tableName
	 * @param string $fieldName
	 * @return integer
	 */
	protected function getModelUidFromAliasCache($alias, $tableName, $fieldName) {
		$clause = "tablename = '" . $tableName . "' AND field_alias = '" . $fieldName . "' AND value_alias = '" . $alias . "'";
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('value_id', 'tx_realurl_uniqalias', $clause);
		if (is_array($rows) && isset($rows[0])) {
			return $rows[0]['value_id'];
		}
		return NULL;
	}

	/**
	 * @param integer $uid
	 * @param string $alias
	 * @param string $tableName
	 * @param string $fieldName
	 * @return void
	 */
	protected function setModelUidAliasCache($uid, $alias, $tableName, $fieldName) {
		$alreadyCached = $this->getModelUidFromAliasCache($alias, $tableName, $fieldName);
		if ($alreadyCached === NULL) {
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_realurl_uniqalias', array(
				'tstamp' => time(),
				'tablename' => $tableName,
				'field_alias' => $fieldName,
				'value_alias' => $alias,
				'value_id' => $uid
			));
		} else {
			$clause = "tablename = '" . $tableName . "' AND field_alias = '" . $fieldName . "'";
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid, value_alias', 'tx_realurl_uniqalias', $clause);
			$row = array_shift($rows);
			$row['value_alias'] = $alias;
			$row['tstamp'] = time();
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_realurl_uniqalias', $clause, $row);
		}
	}

}
