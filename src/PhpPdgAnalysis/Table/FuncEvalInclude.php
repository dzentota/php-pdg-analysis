<?php

namespace PhpPdgAnalysis\Table;

class FuncEvalInclude implements TableInterface {
	public function getValues($cache) {
		$totalFuncs = isset($cache["funcCount"]) && isset($cache['methodCount']) && isset($cache['closureCount']) && isset($cache['scriptCount']) ? $cache['funcCount'] + $cache['methodCount'] + $cache['closureCount'] + $cache['scriptCount'] : null;

		return [
			$cache["name"] ?? "",
			$totalFuncs ?? '',
			'',
			$cache["evalCount"] ?? "",
			$cache["funcsWithEvalCount"] ?? "",
			isset($cache['funcsWithEvalCount']) && isset($totalFuncs) ? number_format($cache['funcsWithEvalCount'] / $totalFuncs * 100, 2) : '',
			'',
			$cache["includeCount"] ?? "",
			$cache["funcsWithIncludeCount"] ?? "",
			isset($cache['funcsWithIncludeCount']) && isset($totalFuncs) ? number_format($cache['funcsWithIncludeCount'] / $totalFuncs * 100, 2) : '',
			'',
			$cache["varVarCount"] ?? "",
			$cache["funcsWithVarVarCount"] ?? "",
			isset($cache['funcsWithVarVarCount']) && isset($totalFuncs) ? number_format($cache['funcsWithVarVarCount'] / $totalFuncs * 100, 2) : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}