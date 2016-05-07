<?php

namespace PhpPdgAnalysis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCommand extends Command {
	private $cacheFile;

	public function __construct($cacheFile) {
		$this->cacheFile = $cacheFile;
		parent::__construct("clear");
	}

	protected function configure() {
		$this
			->setDescription("Clear the analysis cache");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		unlink($this->cacheFile);
	}
}