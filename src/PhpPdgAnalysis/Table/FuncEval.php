<?php

namespace PhpPdgAnalysis\Table;

class FuncEval implements TableInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			$cache["php"] ?? "",
			$cache["funcCount"] ?? "",
			'',
			$cache["funcsWithEvalCount"] ?? "",
			$cache["evalCount"] ?? "",
			'',
			$cache["funcsWithPregEvalCount"] ?? "",
			$cache["pregEvalCount"] ?? "",
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}