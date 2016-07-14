<?php

namespace PhpPdgAnalysis\Analysis;

interface DirectoryAnalysisInterface {
	/**
	 * @param string $librarydir
	 * @return array
	 */
	public function analyse($librarydir);

	/**
	 * @return string[]
	 */
	public function getSuppliedAnalysisKeys();
}