<?php

namespace PhpPdgAnalysis\Table;

class DuplicateNames implements TableInterface {
	public function getValues($cache) {
		return [
			$cache['name'] ?? '',
			$cache['php'] ?? '',
			$cache['autoloading'] ?? '',
			'',
			$cache['classCount'] ?? '',
			$cache['duplicateClassNameCount'] ?? '',
			$cache['duplicateNamespacedClassNameCount'] ?? '',
			'',
			$cache['funcCount'] ?? '',
			$cache['duplicateFuncNameCount'] ?? '',
			$cache['duplicateNamespacedFuncNameCount'] ?? '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}