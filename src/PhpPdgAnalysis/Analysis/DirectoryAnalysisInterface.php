<?php

namespace PhpPdgAnalysis\Analysis;

interface DirectoryAnalysisInterface {
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