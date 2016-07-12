<?php

namespace PhpPdgAnalysis\Table;

class CallOverloading implements TableInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			$cache["release"] ?? "",
			$cache["php"] ?? "",
			'',
			$cache["methodCallCount"] ?? "",
			$cache["__callCount"] ?? "",
			isset($cache['__callCount']) && isset($cache['methodCallCount']) ? number_format($cache['__callCount'] / $cache['methodCallCount'] * 100, 2) : '',
			'',
			$cache['staticCallCount'] ?? "",
			$cache["__callStaticCount"] ?? "",
			isset($cache['__callStaticCount']) && isset($cache['staticCallCount']) ? number_format($cache['__callStaticCount'] / $cache['staticCallCount'] * 100, 2) : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}