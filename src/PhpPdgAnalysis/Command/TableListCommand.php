<?php

namespace PhpPdgAnalysis\Command;

use PhpPdgAnalysis\Table\TableInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TableListCommand extends Command {
	/** @var  TableInterface[] */
	private $tables;

	/**
	 * TableListCommand constructor.
	 * @param TableInterface[] $tables
	 */
	public function __construct(array $tables) {
		$this->tables = $tables;
		parent::__construct("table:list");
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		if (count($this->tables) > 0) {
			echo "available tables:\n";
			foreach ($this->tables as $tableName => $table) {
				echo "    $tableName\n";
			}
		} else {
			echo "no tables available\n";
		}
	}
}