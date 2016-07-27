<?php

namespace PhpPdgAnalysis\Plot;

class EvalMaybeDependences implements PlotInterface {
	public function printPlot($cache) {
		$incomingPlot = [];
		$outgoingPlot = [];
		foreach ($cache as $libraryname => $librarycache) {
			if (isset($librarycache['evalIncomingMaybeDataDependenceCounts']) && isset($librarycache['evalOutgoingMaybeDataDependenceCounts'])) {
				foreach ($librarycache['evalIncomingMaybeDataDependenceCounts'] as $nr => $ct) {
					if (isset($incomingPlot[$nr])) {
						$incomingPlot[$nr] += $ct;
					} else {
						$incomingPlot[$nr] = $ct;
					}
				}
				foreach ($librarycache['evalOutgoingMaybeDataDependenceCounts'] as $nr => $ct) {
					if (isset($outgoingPlot[$nr])) {
						$outgoingPlot[$nr] += $ct;
					} else {
						$outgoingPlot[$nr] = $ct;
					}
				}
			}
		}


		foreach ([$incomingPlot, $outgoingPlot] as $plot) {
			krsort($plot);
			$coordinates = [];
			foreach ($plot as $nr => $ct) {
				$coordinates[] = '(' . $nr . ', ' . $ct . ')';
			}
			echo "\\addplot\n";
			echo 'coordinates {' . implode(' ', $coordinates) . "};\n";
		}
	}
}