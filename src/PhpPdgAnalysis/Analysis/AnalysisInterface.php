<?php

namespace PhpPdgAnalysis\Analysis;

interface AnalysisInterface {
	/**
	 * @return string[];
	 */
	public function getColumnsNames();

	/**
	 * @return string[]
	 */
	public function getSortColumnNames();
}