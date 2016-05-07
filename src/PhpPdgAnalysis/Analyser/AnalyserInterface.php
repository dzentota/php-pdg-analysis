<?php

namespace PhpPdgAnalysis\Analyser;

interface AnalyserInterface {
	/**
	 * @param \SplFileInfo $libraryRootFileInfo
	 * @return array
	 */
	public function analyse(\SplFileInfo $libraryRootFileInfo);

	/**
	 * @return string[]
	 */
	public function getSuppliedAnalysisKeys();
}