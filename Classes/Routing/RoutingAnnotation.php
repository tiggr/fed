<?php
class Tx_Fed_Routing_RoutingAnnotation {

	/**
	 * @var string
	 */
	protected $matchedPattern;

	/**
	 * @var string
	 */
	private $noMatchRulePattern = '/NoMatch\((\'bypass\'|\'null\'|NULL)\)/';

	/**
	 * @var array
	 */
	private $disabledIdentifiers = array('off', 'Off', '0', 'FALSE', 'false', 'no', 'No');

	/**
	 * @param string $matchedPattern
	 * @return void
	 */
	public function setMatchedPattern($matchedPattern) {
		$this->matchedPattern = trim($matchedPattern);
	}

	/**
	 * @return string
	 */
	public function getMatchedPattern() {
		return $this->matchedPattern;
	}

	/**
	 * Assertion: is routing disabled by this annotation
	 *
	 * @return boolean
	 */
	public function assertRoutingDisabled() {
		return in_array($this->matchedPattern, $this->disabledIdentifiers);
	}

	/**
	 * @param string $variableName
	 * @return boolean
	 */
	public function assertAppliesToVariable($variableName) {
		if ($variableName === NULL) {
			return (strpos($this->matchedPattern, '$') === FALSE);
		}
		return (strpos($this->matchedPattern, '$' . $variableName) !== FALSE);
	}

	/**
	 * Get: rule applied when no route segment match is made.
	 * Returns either 'bypass' or 'null' or NULL; a NULL value
	 * means no noMatch rule should be applied.
	 *
	 * @return string|NULL
	 */
	public function getNoMatchRule() {
		$matches = array();
		$matched = preg_match($this->noMatchRulePattern, $this->matchedPattern, $matches);
		if ($matched) {
			$value = trim($matches[1], "'");
			if ($value === 'NULL') {
				$value = NULL;
			}
			return $value;
		}
		return 'bypass';
	}

}