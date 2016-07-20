<?php

namespace PhpPdgAnalysis\Table;

class FuncEval implements TableInterface {
	public function getValues($cache) {
		$totalFuncs = isset($cache["funcCount"]) && isset($cache['methodCount']) && isset($cache['closureCount']) && isset($cache['scriptCount']) ? $cache['funcCount'] + $cache['methodCount'] + $cache['closureCount'] + $cache['scriptCount'] : null;

		return [
			$cache["name"] ?? "",
			$cache["release"] ?? "",
			$cache["php"] ?? "",
			$totalFuncs ?? '',
			'',
			$cache["evalCount"] ?? "",
			isset($cache["evalCount"]) && isset($cache['hillsEval']) ? $cache['evalCount'] - $cache['hillsEval'] : "",
			$cache["funcsWithEvalCount"] ?? "",
			isset($cache['funcsWithEvalCount']) && isset($totalFuncs) ? number_format($cache['funcsWithEvalCount'] / $totalFuncs * 100, 2) : '',
			'',
			$cache["pregEvalCount"] ?? "",
			$cache["funcsWithPregEvalCount"] ?? "",
			isset($cache['funcsWithPregEvalCount']) && isset($totalFuncs) ? number_format($cache['funcsWithPregEvalCount'] / $totalFuncs * 100, 2) : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}