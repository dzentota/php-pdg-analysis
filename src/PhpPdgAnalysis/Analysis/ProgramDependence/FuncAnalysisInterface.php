<?php

namespace PhpPdgAnalysis\Analysis\ProgramDependence;

use PhpPdg\ProgramDependence\Func;

interface FuncAnalysisInterface {
	/**
	 * @param string $libraryname
	 */
	public function enterLibrary($libraryname);

	/**
	 * @param string $libraryname
	 */
	public function leaveLibrary($libraryname);
	/**
	 * @param Func $func
	 * @return array
	 */
	public function analyse(Func $func);
	
	/**
	 * @return array
	 */
	public function getAnalysisResults();

	/**
	 * @return string[]
	 */
	public function getSuppliedAnalysisKeys();
}