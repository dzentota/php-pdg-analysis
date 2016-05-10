<?php

namespace PhpPdgAnalysis\Table;

class FuncRefs implements TableInterface {
	public function getValues($cache) {
		return [
			$cache['name'] ?? '',
			$cache['php'] ?? '',
			$cache['funcCount'],
			'',
			$cache['funcsWithAssignRefCount'] ?? '',
			$cache['assignRefCount'] ?? '',
			'',
			$cache['funcsWithGlobalCount'] ?? '',
			$cache['globalCount'] ?? '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}