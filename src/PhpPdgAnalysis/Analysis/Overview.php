<?php

namespace PhpPdgAnalysis\Analysis;

class Overview implements AnalysisInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			$cache["version"] ?? "",
			$cache["date"] ?? "",
			$cache["php"] ?? "",
			$cache["fileCount"] ?? "",
			$cache["sloc"] ?? "",
			$cache["description"] ?? "",
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}