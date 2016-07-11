<?php

namespace PhpPdgAnalysis\Table;

class FuncVarVar implements TableInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			$cache['release'] ?? '',
			$cache["php"] ?? "",
			$cache["funcCount"] ?? "",
			'',
			$cache["varVarCount"] ?? "",
			$cache["funcsWithVarVarCount"] ?? "",
			isset($cache['funcsWithVarVarCount']) && isset($cache['funcCount']) ? number_format($cache['funcsWithVarVarCount'] / $cache['funcCount'] * 100, 2) : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}