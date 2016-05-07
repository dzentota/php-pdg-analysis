<?php

namespace PhpPdgAnalysis\Analysis;

interface AnalysisInterface {
	/**
	 * @param array $cache
	 * @return array
	 */
	public function getValues($cache);

	/**
	 * @return string[]
	 */
	public function getSortColumns();
}