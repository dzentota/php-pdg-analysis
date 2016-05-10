<?php

namespace PhpPdgAnalysis\Table;

class FuncIncludes implements TableInterface {
	public function getValues($cache) {
		return [
			$cache['name'] ?? '',
			$cache['php'] ?? '',
			$cache['funcCount'],
			'',
			$cache['funcsWithIncludeCount'] ?? '',
			$cache['includeCount'] ?? '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}