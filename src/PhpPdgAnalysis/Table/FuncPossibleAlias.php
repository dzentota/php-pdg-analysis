<?php

namespace PhpPdgAnalysis\Table;

class FuncPossibleAlias implements TableInterface {
	public function getValues($cache) {
		$totalFuncs = isset($cache["funcCount"]) && isset($cache['methodCount']) && isset($cache['closureCount']) && isset($cache['scriptCount']) ? $cache['funcCount'] + $cache['methodCount'] + $cache['closureCount'] + $cache['scriptCount'] : null;

		return [
			$cache["name"] ?? "",
			$totalFuncs ?? '',
			'',
			$cache["assignRefCount"] ?? "",
			$cache["funcsWithAssignRefCount"] ?? "",
			'',
			$cache["globalCount"] ?? "",
			$cache["funcsWithGlobalCount"] ?? "",
			'',
			$cache['funcsWithAnyPossibleAliasCount'] ?? '',
			isset($cache['funcsWithAnyPossibleAliasCount']) && isset($totalFuncs) ? number_format($cache['funcsWithAnyPossibleAliasCount'] / $totalFuncs * 100, 2) : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}