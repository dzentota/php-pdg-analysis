<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\NodeVisitor;

interface AnalysisVisitorInterface extends NodeVisitor {
	/**
	 * @param string $libraryname
	 */
	public function enterLibrary($libraryname);

	/**
	 * @param string $libraryname
	 */
	public function leaveLibrary($libraryname);

	/**
	 * @return array
	 */
	public function getAnalysisResults();

	/**
	 * @return string[]
	 */
	public function getSuppliedAnalysisKeys();
}