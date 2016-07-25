<?php

namespace PhpPdgAnalysis\Table;

class PropertyOverloading implements TableInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			'',
			$cache['__getNodeCount'] ?? '',
			$cache['__getNodesLinkedCount'] ?? '',
			$cache['__getOverloadingCallEdgeCount'] ?? '',
			$cache['__getExplicitCallEdgeCount'] ?? '',
			'',
			$cache['__setNodeCount'] ?? '',
			$cache['__setNodesLinkedCount'] ?? '',
			$cache['__setOverloadingCallEdgeCount'] ?? '',
			$cache['__setExplicitCallEdgeCount'] ?? '',
			'',
			$cache['__issetNodeCount'] ?? '',
			$cache['__issetNodesLinkedCount'] ?? '',
			$cache['__issetOverloadingCallEdgeCount'] ?? '',
			$cache['__issetExplicitCallEdgeCount'] ?? '',
			'',
			$cache['__unsetNodeCount'] ?? '',
			$cache['__unsetNodesLinkedCount'] ?? '',
			$cache['__unsetOverloadingCallEdgeCount'] ?? '',
			$cache['__unsetExplicitCallEdgeCount'] ?? '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}