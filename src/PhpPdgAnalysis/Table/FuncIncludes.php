<?php

namespace PhpPdgAnalysis\Table;

class FuncIncludes implements TableInterface {
	public function getValues($cache) {
		$totalFuncs = isset($cache["funcCount"]) && isset($cache['methodCount']) && isset($cache['closureCount']) && isset($cache['scriptCount']) ? $cache['funcCount'] + $cache['methodCount'] + $cache['closureCount'] + $cache['scriptCount'] : null;

		return [
			$cache['name'] ?? '',
			$cache['release'] ?? '',
			$cache['php'] ?? '',
			$cache['autoloading'] ?? '',
			$totalFuncs ?? '',
			'',
			$cache['includeCount'] ?? '',
			isset($cache['includeCount']) && isset($cache['hillsIncludes']) ? $cache['includeCount'] - $cache['hillsIncludes'] : '',
			$cache['funcsWithIncludeCount'] ?? '',
			isset($cache['funcsWithIncludeCount']) && isset($totalFuncs) ? number_format($cache['funcsWithIncludeCount'] / $totalFuncs * 100, 2) : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}