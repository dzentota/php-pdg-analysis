<?php

namespace PhpPdgAnalysis\Table;

class ResolvedCalls implements TableInterface {
	public function getValues($cache) {
		$totalMethodCalls = isset($cache['methodCallCount']) && isset($cache['staticCallCount']) ? $cache['methodCallCount'] + $cache['staticCallCount'] : null;
		$totalResolvedFuncCalls = isset($cache['resolvedFuncCallNodeCount']) && isset($cache['resolvedNsFuncCallNodeCount']) ? $cache['resolvedFuncCallNodeCount'] + $cache['resolvedNsFuncCallNodeCount']: null;
		$totalResolvedMethodCalls = isset($cache['resolvedMethodCallNodeCount']) && isset($cache['resolvedStaticCallNodeCount']) ? $cache['resolvedMethodCallNodeCount'] + $cache['resolvedStaticCallNodeCount'] : null;

		return [
			$cache["name"] ?? "",
			$cache['funcCallCount'] ?? '',
			$totalMethodCalls ?? '',
			'',
			isset($cache['resolvedFuncCallNodeCount']) && isset($cache['resolvedNsFuncCallNodeCount']) ? $cache['resolvedFuncCallNodeCount'] + $cache['resolvedNsFuncCallNodeCount']: '',
			isset($cache['funcCallCount']) && $totalResolvedFuncCalls !== null ? number_format(($totalResolvedFuncCalls / $cache['funcCallCount']) * 100, 2) : '',
			'',
			$totalResolvedMethodCalls ?? '',
			$totalMethodCalls !== null && $totalResolvedMethodCalls !== null ? number_format(($totalResolvedMethodCalls / $totalMethodCalls) * 100, 2) : '',
			'',
			$cache['callEdgeToFuncCount'] ?? '',
			$cache['callEdgeToBuiltinFuncCount'] ?? '',
			isset($cache['callEdgeToUndefinedFuncCount']) && isset($cache['callEdgeToUndefinedNsFuncCount']) ? $cache['callEdgeToUndefinedFuncCount'] + $cache['callEdgeToUndefinedNsFuncCount'] : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}