<?php

namespace PhpPdgAnalysis\Table;

class DuplicateNames implements TableInterface {
	public function getValues($cache) {
		if (isset($cache['duplicateNamespacedFuncNameCount']) && isset($cache['funcCount'])) {

		}

		return [
			$cache['name'] ?? '',
//			$cache['php'] ?? '',
			$cache['fileCount'] ?? '',
//			$cache['autoloading'] ?? '',
			'',
			$cache['funcCount'] ?? '',
			$cache['duplicateNamespacedFuncNameCount'] ?? '',
			isset($cache['duplicateNamespacedFuncNameCount']) && isset($cache['funcCount']) ? $cache['funcCount'] > 0 ? number_format($cache['duplicateNamespacedFuncNameCount'] / $cache['funcCount'] * 100, 2) : '0.00' : '',
			'',
			$cache['classCount'] ?? '',
			$cache['duplicateNamespacedClassNameCount'] ?? '',
			isset($cache['duplicateNamespacedClassNameCount']) && isset($cache['classCount']) ? number_format($cache['duplicateNamespacedClassNameCount'] / $cache['classCount'] * 100, 2) : '',

		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}