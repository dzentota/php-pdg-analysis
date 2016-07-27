<?php

namespace PhpPdgAnalysis\Command;

use PhpPdgAnalysis\Plot\PlotInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PlotListCommand extends Command {
	/** @var  PlotInterface[] */
	private $plots;

	/**
	 * PltListCommand constructor.
	 * @param PlotInterface[] $plots
	 */
	public function __construct(array $plots) {
		$this->plots = $plots;
		parent::__construct("plot:list");
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		if (count($this->plots) > 0) {
			echo "available plots:\n";
			foreach ($this->plots as $plotName => $plot) {
				echo "    $plotName\n";
			}
		} else {
			echo "no plots available\n";
		}
	}
}