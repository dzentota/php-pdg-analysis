<?php

namespace PhpPdgAnalysis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CallPairsFromTraceCommand extends Command {
	public function __construct() {
		parent::__construct("call-pairs:from-trace");
	}

	protected function configure() {
		$this
			->setDescription("Generate a call pairs file from a function trace")
			->addArgument(
				'traceFile',
				InputArgument::REQUIRED,
				'Path to root dir of system to generate SDG for'
			)
			->addArgument(
				'outputFile',
				InputArgument::REQUIRED,
				'Path to output compiled call pairs to'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$traceFile = $input->getArgument('traceFile');
		$outputFile = $input->getArgument('outputFile');

		if (is_file($traceFile) === false) {
			die("Tracefile does not exist");
		}

		$f = fopen($traceFile, 'r');
		while (strpos(($l = fgets($f)), 'TRACE START') !== 0) {}

		$starttime = microtime(true);
		$stack = [];
		$currentfunc = null;
		$out = [];
		$recordct = 0;
		while (($row = fgetcsv($f, null, "\t")) !== false) {
			if (isset($row[2])) {
				switch ($row[2]) {
					case '0':
						$func = $row[5];
						$file = $row[8];
						$line = $row[9];
						if ($currentfunc !== null) {
							if (isset($out[$file][$line][$func])) {
								$out[$file][$line][$func]++;
							} else {
								$out[$file][$line][$func] = 1;
							}
						}
						$stack[] = $currentfunc;
						$currentfunc = $func;
						break;
					case '1':
						$currentfunc = array_pop($stack);
						break;
					case 'R':
						break;
				}
				if (++$recordct % 100000 === 0) {
					echo "$recordct\n";
				}
			}
		}

		$callpairct = 0;
		foreach ($out as $file => $lines) {
			foreach ($lines as $tofuncs) {
				$callpairct += count($tofuncs);
			}
		}
		file_put_contents($outputFile, json_encode($out, JSON_PRETTY_PRINT));
		$runtime = microtime(true) - $starttime;
		echo "Total: $recordct records, containing $callpairct unique call pairs\n";
		echo sprintf("Time: %0.2fs\n", $runtime);
	}
}