<?php

namespace PhpPdgAnalysis\Plot;

class ResolvedCallCounts implements PlotInterface {
	public function printPlot($cache) {

		foreach ($cache as $libraryName => $libraryCache) {
			if (
				isset($cache['resolvedFuncCallEdgeCounts'])
				&& isset($cache['resolvedNsFuncCallEdgeCounts'])
				&& isset($cache['resolvedMethodCallEdgeCounts'])
				&& isset($cache['resolvedStaticCallEdgeCounts'])
			) {

			}
		}
	}
}