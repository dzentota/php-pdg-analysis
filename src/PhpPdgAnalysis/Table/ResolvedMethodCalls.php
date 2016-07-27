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