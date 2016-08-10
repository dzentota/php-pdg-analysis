<?php

namespace PhpPdgAnalysis\Table;

class CallFeatures implements TableInterface {
	public function getValues($cache) {
		$totalMethodCalls = isset($cache['methodCallCount']) && isset($cache['staticCallCount']) ? $cache['methodCallCount'] + $cache['staticCallCount'] : null;
		return [
			$cache["name"] ?? "",
//			$cache['php'] ?? '',
			$cache['fileCount'] ?? '',
			'',
			$cache['funcCount'] ?? '',
			$cache['funcCallCount'] ?? '',
			'',
			$cache['classCount'] ?? '',
			'',
			$cache['methodCount'] ?? '',
			$totalMethodCalls ?? '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}