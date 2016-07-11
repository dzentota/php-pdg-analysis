<?php

namespace PhpPdgAnalysis\Table;

class FuncEval implements TableInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			$cache["release"] ?? "",
			$cache["php"] ?? "",
			$cache["funcCount"] ?? "",
			'',
			$cache["evalCount"] ?? "",
			$cache["funcsWithEvalCount"] ?? "",
			isset($cache['funcsWithEvalCount']) && isset($cache['funcCount']) ? number_format($cache['funcsWithEvalCount'] / $cache['funcCount'] * 100, 2) : '',
			'',
			$cache["pregEvalCount"] ?? "",
			$cache["funcsWithPregEvalCount"] ?? "",
			isset($cache['funcsWithPregEvalCount']) && isset($cache['funcCount']) ? number_format($cache['funcsWithPregEvalCount'] / $cache['funcCount'] * 100, 2) : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}