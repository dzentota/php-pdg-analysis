<?php

namespace PhpPdgAnalysis\Table;

class DataDependences implements TableInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			$cache['opNodeCount'] ?? '',
			'',
			$cache['operandCount'] ?? '',
			$cache['literalOperandCount'] ?? '',
			$cache['boundVariableOperandCount'] ?? '',
			isset($cache['operandCount']) && isset($cache['literalOperandCount']) && isset($cache['boundVariableOperandCount']) ? $cache['operandCount'] - $cache['literalOperandCount'] - $cache['boundVariableOperandCount'] : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}