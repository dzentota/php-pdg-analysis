<?php

namespace PhpPdgAnalysis\Table;

class Overview implements TableInterface {
	public function getValues($cache) {
		return [
			$cache["name"] ?? "",
			$cache["version"] ?? "",
			$cache["release"] ?? "",
			$cache["php"] ?? "",
			$cache["fileCount"] ?? "",
			$cache["sloc"] ?? "",
			$cache["description"] ?? "",
		];
	}

	public function getSortColumns() {
		return [
			[0,1]
		];
	}
}