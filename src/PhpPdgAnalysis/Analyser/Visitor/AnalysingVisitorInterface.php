<?php

namespace PhpPdgAnalysis\Analyser\Visitor;

use PhpParser\NodeVisitor;

interface AnalysingVisitorInterface extends NodeVisitor {
	/**
	 * @return array
	 */
	public function getAnalysisResults();

	/**
	 * @return string[]
	 */
	public function getSuppliedAnalysisKeys();
}