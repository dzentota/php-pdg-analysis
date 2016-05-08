<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\NodeVisitor;

interface AnalysisVisitorInterface extends NodeVisitor {
	/**
	 * @return array
	 */
	public function getAnalysisResults();

	/**
	 * @return string[]
	 */
	public function getSuppliedAnalysisKeys();
}