<?php

namespace PhpPdgAnalysis\Table;

class FuncException implements TableInterface {
	public function getValues($cache) {
		$totalFuncs = isset($cache["funcCount"]) && isset($cache['methodCount']) && isset($cache['closureCount']) && isset($cache['scriptCount']) ? $cache['funcCount'] + $cache['methodCount'] + $cache['closureCount'] + $cache['scriptCount'] : null;

		return [
			$cache["name"] ?? "",
			$totalFuncs ?? '',
			'',
			$cache["tryCount"] ?? "",
			$cache["funcsWithTryCount"] ?? "",
			isset($cache['funcsWithTryCount']) && isset($totalFuncs) ? number_format($cache['funcsWithTryCount'] / $totalFuncs * 100, 2) : '',
			'',
			$cache["throwCount"] ?? "",
			$cache["funcsWithThrowCount"] ?? "",
			isset($cache['funcsWithThrowCount']) && isset($totalFuncs) ? number_format($cache['funcsWithThrowCount'] / $totalFuncs * 100, 2) : '',
			'',
			$cache['funcsWithTryAndThrowCount'] ?? '',
			isset($cache['funcsWithTryAndThrowCount']) && isset($totalFuncs) ? number_format($cache['funcsWithTryAndThrowCount'] / $totalFuncs * 100, 2) : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}