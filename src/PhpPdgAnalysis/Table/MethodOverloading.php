<?php

namespace PhpPdgAnalysis\Table;

class MethodOverloading implements TableInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			'',
			$cache['__callNodeCount'] ?? '',
			$cache['__callNodesLinkedCount'] ?? '',
			$cache['__callOverloadingCallEdgeCount'] ?? '',
			$cache['__callExplicitCallEdgeCount'] ?? '',
			'',
			$cache['__callStaticNodeCount'] ?? '',
			$cache['__callStaticNodesLinkedCount'] ?? '',
			$cache['__callStaticOverloadingCallEdgeCount'] ?? '',
			$cache['__callStaticExplicitCallEdgeCount'] ?? '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}