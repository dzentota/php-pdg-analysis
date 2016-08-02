<?php

namespace PhpPdgAnalysis\Table;

class AnalysisResult implements TableInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			$cache['php'] ?? '',
			$cache["fileCount"] ?? "",
			'',
			$cache['errorFileCt'] ?? '',
			isset($cache['errorFileCt']) && isset($cache["fileCount"]) ? number_format($cache['errorFileCt'] / $cache["fileCount"] * 100, 2) : '',
			$cache['maxComplexityExceededFileCt'] ?? '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}