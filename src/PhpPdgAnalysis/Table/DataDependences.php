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
			$cache['operandWithWriteOpCount'] ?? '',
			isset($cache['operandCount']) && isset($cache['literalOperandCount']) && isset($cache['boundVariableOperandCount']) && isset($cache['operandWithWriteOpCount']) ? $cache['operandCount'] - $cache['literalOperandCount'] - $cache['boundVariableOperandCount'] - $cache['operandWithWriteOpCount'] : '',
			'',
			$cache['resolvedOperandcount'] ?? '',
			isset($cache['resolvedOperandcount']) && isset($cache['operandWithWriteOpCount']) ? number_format($cache['resolvedOperandcount'] / $cache['operandWithWriteOpCount'] * 100, 2) : '',
			'',
			$cache['dataDependenceEdgeCount'] ?? '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}