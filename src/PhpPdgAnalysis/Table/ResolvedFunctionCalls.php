<?php

namespace PhpPdgAnalysis\Table;

class ResolvedFunctionCalls implements TableInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			$cache['funcCallNodes'] ?? '',
			'',
			$cache['resolvedFuncCallNodeCount'] ?? '',
			isset($cache['funcCallNodes']) && isset($cache['resolvedFuncCallNodeCount']) ? number_format(($cache['resolvedFuncCallNodeCount'] / $cache['funcCallNodes']) * 100, 2) : '',
			'',
			$cache['funcCallUnresolvedDueToVariableFuncCall'],
			$cache['funcCallUnresolvedDueToOther'],
			'',
			$cache['funcCallEdgeToFuncCount'] ?? '',
			$cache['funcCallEdgeToBuiltinFuncCount'] ?? '',
			$cache['funcCallEdgeToUndefinedFuncCount'] ?? '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}