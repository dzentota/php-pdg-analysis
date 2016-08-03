<?php

namespace PhpPdgAnalysis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CallPairsCompareCommand extends Command {
	public function __construct() {
		parent::__construct("call-pairs:compare");
	}

	protected function configure() {
		$this
			->setDescription("Compare an SDG and trace call pairs file to see which trace calls are not in the SDG")
			->addArgument(
				'sdgPairsFile',
				InputArgument::REQUIRED,
				'Path to root dir of system to generate SDG for'
			)
			->addArgument(
				'tracePairsFile',
				InputArgument::REQUIRED,
				'Path to output compiled call pairs to'
			)
			->addOption(
				"sdgFilePrefix",
				null,
				InputOption::VALUE_REQUIRED,
				"Prefix to strip off sdg file paths before matching",
				''
			)
			->addOption(
				"traceFilePrefix",
				null,
				InputOption::VALUE_REQUIRED,
				"Prefix to strip off trace file paths before matching",
				''
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$starttime = microtime(true);
		$sdgPairsFile = $input->getArgument('sdgPairsFile');
		$tracePairsFile = $input->getArgument('tracePairsFile');
		$sdgFilePrefix = $input->getOption('sdgFilePrefix');
		$traceFilePrefix = $input->getOption('traceFilePrefix');

		if (is_file($sdgPairsFile) === false) {
			die('sdg pairs file does not exist');
		}
		if (is_file($tracePairsFile) === false) {
			die('trace pairs file does not exist');
		}

		$sdgPairs = json_decode(file_get_contents($sdgPairsFile), true);
		$tracePairs = json_decode(file_get_contents($tracePairsFile), true);

		// canonicalize trace pairs method calls first as the sdg does not distinguish between static and instance calls
		foreach ($tracePairs as $file => $lines) {
			$file = preg_replace('/^' . preg_quote($traceFilePrefix) . '/', '', $file);
			foreach ($lines as $line => $funcs) {
				foreach ($funcs as $func => $callct) {
					$tracePairs[$file][$line][strtolower(str_replace('->', '::', $func))] = $callct;
				}
			}
		}
		// the sdg pairs do not all lowercase the func name, probably fix this in the generation?
		foreach ($sdgPairs['calls'] as $file => $lines) {
			$file = preg_replace('/^' . preg_quote($sdgFilePrefix) . '/', '', $file);
			foreach ($lines as $line => $funcs) {
				foreach ($funcs as $func => $ct) {
					$sdgPairs['calls'][$file][$line][strtolower($func)] = $ct;
				}
			}
		}
		foreach ($sdgPairs['funcs'] as $func => $ct) {
			$sdgPairs['funcs'][strtolower($func)] = 1;
		}
		foreach ($sdgPairs['builtin-funcs'] as $builtinfunc => $ct) {
			$sdgPairs['builtin-funcs'][strtolower($builtinfunc)] = 1;
		}

		$foundCount = 0;
		$constructorCount = 0;
		$toStringCount = 0;
		$countCount = 0;
		$sleepCount = 0;
		$wakeupCount = 0;
		$arrayAccessCount = 0;
		$missingCount = 0;
		$undefinedCount = 0;
		foreach ($sdgPairs['calls'] as $file => $lines) {
			foreach ($lines as $line => $sdgFuncs) {
				if (isset($tracePairs[$file][$line]) === true) {
					$traceFuncs = $tracePairs[$file][$line];
					foreach ($traceFuncs as $func => $ct) {
						if (isset($sdgPairs['funcs'][$func]) || isset($sdgPairs['builtin-funcs'][$func])) {
							if (isset($sdgFuncs[$func]) === true) {
								$foundCount++;
							} else {
								if (preg_match('/\_\_construct$/', $func) === 1) {
									$constructorCount++;
								} else if (preg_match('/\_\_tostring$/', $func) === 1) {
									$toStringCount++;
								} else if (preg_match('/count$/', $func) === 1) {
									$countCount++;
								} else if (preg_match('/\_\_sleep$/', $func) === 1) {
									$sleepCount++;
								} else if (preg_match('/\_\_wakeup$/', $func) === 1) {
									$wakeupCount++;
								} else if (preg_match('/(offsetget|offsetset|offsetexists|offsetunset)$/', $func) === 1) {
									$arrayAccessCount++;
								} else {
									$missingCount++;
								}
							}
						} else {
							$undefinedCount++;
						}
					}
				}
			}
		}

		echo "found: $foundCount\n";
		echo "constructor: $constructorCount\n";
		echo "toString: $toStringCount\n";
		echo "count: $countCount\n";
		echo "sleep: $sleepCount\n";
		echo "wakeup: $wakeupCount\n";
		echo "arrayAccess: $arrayAccessCount\n";
		echo "missing: $missingCount\n";
		echo "undefined: $undefinedCount\n";
		echo sprintf("Time: %0.2fs\n", microtime(true) - $starttime);
	}
}