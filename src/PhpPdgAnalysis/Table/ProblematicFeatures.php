<?php

namespace PhpPdgAnalysis\Table;

class ProblematicFeatures implements TableInterface {
	public function getValues($cache) {
		return [
			$cache['name'] ?? '',
			$cache['php'] ?? '',
			$cache['funcCount'],
			'',
			$cache['funcsWithVarVarCount'] ?? '',
			$cache['varVarCount'] ?? '',
			'',
			$cache['funcsWithEvalCount'] ?? '',
			$cache['evalCount'] ?? '',
			'',
			$cache['funcsWithIncludeCount'] ?? '',
			$cache['includeCount'] ?? '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}