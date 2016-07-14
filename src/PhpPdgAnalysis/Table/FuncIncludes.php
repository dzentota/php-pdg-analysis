<?php

namespace PhpPdgAnalysis\Table;

class FuncIncludes implements TableInterface {
	public function getValues($cache) {
		return [
			$cache['name'] ?? '',
			$cache['release'] ?? '',
			$cache['php'] ?? '',
			$cache['autoloading'] ?? '',
			isset($cache["funcCount"]) && isset($cache['methodCount']) && isset($cache['closureCount']) && isset($cache['scriptCount']) ? $cache['funcCount'] + $cache['methodCount'] + $cache['closureCount'] + $cache['scriptCount'] : '',
			'',
			$cache['includeCount'] ?? '',
			$cache['funcsWithIncludeCount'] ?? '',
			isset($cache['funcsWithIncludeCount']) && isset($cache['funcCount']) ? number_format($cache['funcsWithIncludeCount'] / $cache['funcCount'] * 100, 2) : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}