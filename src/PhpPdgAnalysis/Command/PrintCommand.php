<?php

namespace PhpPdgAnalysis\Command;

use PhpPdgAnalysis\Analysis\AnalysisInterface;
use PhpPdgAnalysis\Analysis\Overview;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PrintCommand extends Command {
	private $cacheFile;
	/** @var AnalysisInterface[] */
	private $analyses;

	public function __construct($cacheFile) {
		$this->cacheFile = $cacheFile;
		$this->analyses = [
			"overview" => new Overview(),
		];
		parent::__construct("print");
	}

	protected function configure() {
		$this
			->setDescription("Print an analysis for export to latex")
			->addArgument(
				"analysis",
				InputOption::VALUE_REQUIRED,
				"Which analysis should be printed"
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$cache = json_decode(file_get_contents($this->cacheFile), true);
		$analysis_name = $input->getArgument("analysis");
		if (!isset($this->analyses[$analysis_name])) {
			throw new \RuntimeException("No such analysis");
		}
		$analysis = $this->analyses[$analysis_name];
		$columnNames = $analysis->getColumnsNames();
		$sortColumnNames = $analysis->getSortColumnNames();

		// extract wanted results
		$rows = [];
		$maxLengths = [];
		foreach ($cache as $results) {
			$row = [];
			foreach ($columnNames as $i => $columnName) {
				$value = isset($results[$columnName]) ? $results[$columnName] : "";
				$valueLength = strlen($value);
				if (!isset($maxLengths[$i]) || $maxLengths[$i] < $valueLength) {
					$maxLengths[$i] = $valueLength;
				}
				$row[] = $value;
			}
			$rows[] = $row;
		}

		usort($rows, function ($a, $b) use ($sortColumnNames) {
			foreach ($sortColumnNames as list($sortColumnName,$order)) {
				$aValue = isset($a[$sortColumnName]) ? $a[$sortColumnName] : "";
				$bValue = isset($b[$sortColumnName]) ? $b[$sortColumnName] : "";
				$compValue = strnatcasecmp($aValue, $bValue) * $order;
				if ($compValue !== 0) {
					return $compValue;
				}
			}
			return 0;
		});

		$str = "";
		foreach ($rows as $values) {
			$paddedValues = [];
			foreach ($values as $i => $value) {
				$paddedValues[] = str_pad($value, $maxLengths[$i]);
			}
			$str .= '    ' . implode(" & ", $paddedValues) . "\\\\\n";
		}
		echo $str;
	}
}