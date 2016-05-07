<?php

namespace PhpPdgAnalysis\Analysis;

class Overview implements AnalysisInterface {
	public function getColumnsNames() {
		return [
			"name",
			"version",
			"date",
			"php",
			"fileCount",
			"sloc",
			"description",
		];
	}

	public function getSortColumnNames() {
		return [
			[0,1]
		];
	}
}