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
	#private $noMatchRulePattern = '/NoMatch[(](a-zA-Z\\\')[)+]/';

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
		$disabledIdentifiers = array('off', 'Off', '0', 'FALSE', 'false', 'no', 'No');
		return in_array($this->matchedPattern, $disabledIdentifiers);
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
		return NULL;
	}

}