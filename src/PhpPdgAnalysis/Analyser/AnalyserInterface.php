<?php

namespace PhpPdgAnalysis\Analyser;

interface AnalyserInterface {
	/**
	 * @param \SplFileInfo $libraryPath
	 * @return array
	 */
	public function analyse(\SplFileInfo $libraryPath);
}