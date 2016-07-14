<?php

namespace PhpPdgAnalysis\Table;

class CallOverloading implements TableInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			$cache["release"] ?? "",
			$cache["php"] ?? "",
			$cache["classCount"] ?? "",
			'',
			$cache["__callCount"] ?? "",
			isset($cache['__callCount']) && isset($cache['classCount']) ? number_format($cache['__callCount'] / $cache['classCount'] * 100, 2) : '',
			'',
			$cache["__callStaticCount"] ?? "",
			isset($cache['__callStaticCount']) && isset($cache['classCount']) ? number_format($cache['__callStaticCount'] / $cache['classCount'] * 100, 2) : '',
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}