<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\NodeVisitor;

interface AnalysisVisitorInterface extends NodeVisitor {
	public function enterLibrary();

	public function leaveLibrary();

	/**
	 * @return array
	 */
	public function getAnalysisResults();

	/**
	 * @return string[]
	 */
	public function getSuppliedAnalysisKeys();
}