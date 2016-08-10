<?php

namespace PhpPdgAnalysis\Table;

class DataDependences2 implements TableInterface {
	public function getValues($cache) {
		$resolvableOperands = isset($cache['operandCount']) && isset($cache['literalOperandCount']) && isset($cache['boundVariableOperandCount']) ? $cache['operandCount'] - $cache['literalOperandCount'] - $cache['boundVariableOperandCount'] : null;

		return [
			$cache["name"] ?? "",
			$resolvableOperands ?? '',
			'',
			$cache['resolvedOperandcount'] ?? '',
			isset($cache['resolvedOperandcount']) && isset($resolvableOperands) ? number_format($cache['resolvedOperandcount'] / $resolvableOperands * 100, 2) : '',
			'',
			$cache['predefinedVariables'] ?? '',
			$cache['undefinedVariables'] ?? '',
			$cache['unresolvedDueToNotInGraphCount'] ?? '',
			$cache['dynamicFeatureCount'] ?? '',
//			isset($resolvableOperands) && isset($cache['resolvedOperandcount']) && isset($cache['predefinedVariables']) && isset($cache['undefinedVariables']) && isset($cache['unresolvedDueToNotInGraphCount']) && isset($cache['dynamicFeatureCount']) ? $resolvableOperands - $cache['resolvedOperandcount'] - $cache['predefinedVariables'] - $cache['undefinedVariables'] - $cache['unresolvedDueToNotInGraphCount'] - $cache['dynamicFeatureCount'] : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}