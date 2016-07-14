<?php

namespace PhpPdgAnalysis\Table;

class DynamicCalls implements TableInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			$cache["release"] ?? "",
			$cache["php"] ?? "",
			'',
			$cache["funcCallCount"] ?? "",
			$cache["varFuncCallCount"] ?? "",
			isset($cache['varFuncCallCount']) && isset($cache['funcCallCount']) ? number_format($cache['varFuncCallCount'] / $cache['funcCallCount'] * 100, 2) : '',
			'',
			$cache["methodCallCount"] ?? "",
			$cache["varMethodCallCount"] ?? "",
			isset($cache['varMethodCallCount']) && isset($cache['methodCallCount']) ? number_format($cache['varMethodCallCount'] / $cache['methodCallCount'] * 100, 2) : '',
			'',
			$cache['staticCallCount'] ?? "",
			$cache["varStaticMethodCallCount"] ?? "",
			isset($cache['varStaticMethodCallCount']) && isset($cache['staticCallCount']) ? number_format($cache['varStaticMethodCallCount'] / $cache['staticCallCount'] * 100, 2) : '',
			'',
			isset($cache['callUserFuncCount']) && isset($cache['callUserFuncArrayCount']) ? $cache['callUserFuncCount'] + $cache['callUserFuncArrayCount'] : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}