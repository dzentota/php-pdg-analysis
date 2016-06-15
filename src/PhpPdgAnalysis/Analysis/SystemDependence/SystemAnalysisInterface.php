<?php

namespace PhpPdgAnalysis\Analysis\SystemDependence;

use PhpPdg\SystemDependence\System;

interface SystemAnalysisInterface {
	/**
	 * @param System $system
	 * @return array
	 */
	public function analyse(System $system);

	/**
	 * @return string[]
	 */
	public function getSuppliedAnalysisKeys();
}