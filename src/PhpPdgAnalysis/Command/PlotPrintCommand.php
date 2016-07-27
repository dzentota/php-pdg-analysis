<?php

namespace PhpPdgAnalysis\Command;

use PhpPdgAnalysis\Plot\PlotInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PlotPrintCommand extends Command {
	private $cacheFile;
	/** @var PlotInterface[] */
	private $plots;

	public function __construct($cacheFile, $plots) {
		$this->cacheFile = $cacheFile;
		$this->plots = $plots;
		parent::__construct("plot:print");
	}

	protected function configure() {
		$this
			->setDescription("Print a plot for export to latex")
			->addArgument(
				"plot",
				InputOption::VALUE_REQUIRED,
				"Which plot should be printed"
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$cache = json_decode(file_get_contents($this->cacheFile), true);
		$plotName = $input->getArgument("plot");
		if (!isset($this->plots[$plotName])) {
			throw new \RuntimeException("No such plot");
		}

		$this->plots[$plotName]->printPlot($cache);
	}
}