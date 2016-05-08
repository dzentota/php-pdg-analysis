<?php

namespace PhpPdgAnalysis\Command;

use PhpPdgAnalysis\Analysis\DirectoryAnalysisInterface;
use PhpPdgAnalysis\Analysis\Visitor\AnalysisVisitorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnalysisListCommand extends Command {
	/** @var DirectoryAnalysisInterface[]  */
	private $directoryAnalyses;
	/** @var AnalysisVisitorInterface[]  */
	private $analyisisVisitors;

	/**
	 * AnalysisListCommand constructor.
	 * @param DirectoryAnalysisInterface[] $directoryAnalyses
	 * @param AnalysisVisitorInterface[] $analysisVisitors
	 */
	public function __construct(array $directoryAnalyses, array $analysisVisitors) {
		$this->directoryAnalyses = $directoryAnalyses;
		$this->analyisisVisitors = $analysisVisitors;
		parent::__construct("analysis:list");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		if (count($this->directoryAnalyses) > 0) {
			echo "available directory analyses:\n";
			foreach ($this->directoryAnalyses as $analysisName => $directoryAnalysis) {
				echo "    $analysisName\n";
			}
		} else {
			echo "no directory analyses available\n";
		}
		echo "\n";
		if (count($this->analyisisVisitors) > 0) {
			echo "available analysis visitors:\n";
			foreach ($this->analyisisVisitors as $analysisName => $analyisisVisitor) {
				echo "    $analysisName\n";
			}
		} else {
			echo "no analysis visitors available\n";
		}
	}
}