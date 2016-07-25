<?php

namespace PhpPdgAnalysis\Table;

class ResolvedMethodCalls implements TableInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			$cache['methodCallNodes'] ?? '',
			'',
			$cache['resolvedMethodCallNodeCount'] ?? '',
			isset($cache['methodCallNodes']) && isset($cache['resolvedMethodCallNodeCount']) ? number_format(($cache['resolvedMethodCallNodeCount'] / $cache['methodCallNodes']) * 100, 2) : '',
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