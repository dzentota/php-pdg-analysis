<?php

namespace PhpPdgAnalysis\Command;

use PhpPdgAnalysis\Table\TableInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TablePrintCommand extends Command {
	private $cacheFile;
	/** @var TableInterface[] */
	private $tables;

	public function __construct($cacheFile, $tables) {
		$this->cacheFile = $cacheFile;
		$this->tables = $tables;
		parent::__construct("table:print");
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
		if (!isset($this->tables[$analysis_name])) {
			throw new \RuntimeException("No such analysis");
		}
		$analysis = $this->tables[$analysis_name];
		$sortColumns = $analysis->getSortColumns();

		$rows = [];
		$maxLengths = [];
		foreach ($cache as $results) {
			$values = $analysis->getValues($results);
			foreach ($values as $i => $value) {
				$valueLength = strlen($value);
				if (!isset($maxLengths[$i]) || $maxLengths[$i] < $valueLength) {
					$maxLengths[$i] = $valueLength;
				}
			}
			$rows[] = $values;
		}

		usort($rows, function ($a, $b) use ($sortColumns) {
			foreach ($sortColumns as list($sortColumnName,$order)) {
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