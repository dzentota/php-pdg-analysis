<?php

namespace PhpPdgAnalysis\Table;

class DynamicCalls implements TableInterface {
	public function getValues($cache) {
		$totalFuncs = isset($cache["funcCount"]) && isset($cache['methodCount']) && isset($cache['closureCount']) && isset($cache['scriptCount']) ? $cache['funcCount'] + $cache['methodCount'] + $cache['closureCount'] + $cache['scriptCount'] : null;
		$totalFuncsWithVarMethodCalls = isset($cache['funcsWithVarMethodCallCount']) && isset($cache['funcsWithVarStaticMethodCallCount']) ? $cache['funcsWithVarMethodCallCount'] + $cache['funcsWithVarStaticMethodCallCount'] : null;
		$totalVarMethodCalls = isset($cache['varMethodCallCount']) && isset($cache['varStaticMethodCallCount']) ? $cache['varMethodCallCount'] + $cache['varStaticMethodCallCount'] : null;

		return [
			$cache["name"] ?? "",
			$totalFuncs ?? "",
//			$cache["release"] ?? "",
//			$cache["php"] ?? "",
			'',
			$cache["varFuncCallCount"] ?? "",
			$cache["funcsWithVarFuncCallCount"] ?? "",
			isset($cache['funcsWithVarFuncCallCount']) && isset($totalFuncs) ? number_format($cache['funcsWithVarFuncCallCount'] / $totalFuncs * 100, 2) : '',
			'',
			$totalVarMethodCalls ?? '',
			$totalFuncsWithVarMethodCalls ?? '',
			isset($totalFuncsWithVarMethodCalls) && isset($totalFuncs) ? number_format($totalFuncsWithVarMethodCalls / $totalFuncs * 100, 2) : '',
			'',
			$cache['callUserFuncCount'] ?? "",
			$cache["funcsWithCallUserFuncCount"] ?? "",
			isset($cache['funcsWithCallUserFuncCount']) && isset($totalFuncs) ? number_format($cache['funcsWithCallUserFuncCount'] / $totalFuncs * 100, 2) : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}