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
			$cache['duplicateNamespacedClassNameCount'] ?? '',
			isset($cache['duplicateNamespacedClassNameCount']) && isset($cache['classCount']) ? number_format($cache['duplicateNamespacedClassNameCount'] / $cache['classCount'] * 100, 2) : '',
			'',
			$cache['funcCount'] ?? '',
			$cache['duplicateNamespacedFuncNameCount'] ?? '',
			isset($cache['duplicateNamespacedFuncNameCount']) && isset($cache['funcCount']) ? number_format($cache['duplicateNamespacedFuncNameCount'] / $cache['funcCount'] * 100, 2) : '',

		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}