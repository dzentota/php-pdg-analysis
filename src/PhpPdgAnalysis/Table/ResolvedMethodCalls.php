<?php

namespace PhpPdgAnalysis\Table;

class ResolvedMethodCalls implements TableInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			$cache['methodCallNodes'] ?? '',
			'',
			$cache['typedMethodCallNodes'] ?? '',
			isset($cache['methodCallNodes']) && isset($cache['typedMethodCallNodes']) ? number_format(($cache['typedMethodCallNodes'] / $cache['methodCallNodes']) * 100, 2) : '',
			'',
			$cache['resolvedMethodCallNodeCount'] ?? '',
			isset($cache['typedMethodCallNodes']) && isset($cache['resolvedMethodCallNodeCount']) ? number_format(($cache['resolvedMethodCallNodeCount'] / $cache['typedMethodCallNodes']) * 100, 2) : '',
			'',
			// This is necessary because of an error made in the analysis, where unresolved due to dyanmic is counted before unresolved due to untyped. As we know that methods unresolved due to other is 0, we can be certain that this is correct.
			isset($cache['typedMethodCallNodes']) && isset($cache['resolvedMethodCallNodeCount']) ? $cache['typedMethodCallNodes'] - $cache['resolvedMethodCallNodeCount']: '',
			$cache['methodCallUnresolvedDueToOther'],
			'',
			$cache['methodCallEdgeToFuncCount'] ?? '',
			$cache['methodCallEdgeToBuiltinFuncCount'] ?? '',
			$cache['methodCallEdgeToUndefinedFuncCount'] ?? '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}