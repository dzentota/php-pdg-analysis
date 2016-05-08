<?php

namespace PhpPdgAnalysis\Table;

interface TableInterface {
	/**
	 * @param array $cache
	 * @return array
	 */
	public function getValues($cache);

	/**
	 * @return string[]
	 */
	public function getSortColumns();
}