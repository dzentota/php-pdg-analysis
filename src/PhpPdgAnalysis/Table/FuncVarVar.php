<?php

namespace PhpPdgAnalysis\Table;

class FuncVarVar implements TableInterface {
	public function getValues($cache) {
		$totalFuncs = isset($cache["funcCount"]) && isset($cache['methodCount']) && isset($cache['closureCount']) && isset($cache['scriptCount']) ? $cache['funcCount'] + $cache['methodCount'] + $cache['closureCount'] + $cache['scriptCount'] : null;

		return [
			$cache["name"] ?? "",
			$cache['release'] ?? '',
			$cache["php"] ?? "",
			$totalFuncs ?? '',
			'',
			$cache["varVarCount"] ?? "",
			isset($cache['varVarCount']) && isset($cache['hillsVarVar']) ? $cache['varVarCount'] - $cache['hillsVarVar'] : '',
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