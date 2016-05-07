<?php

namespace PhpPdgAnalysis\Command;

use PhpPdgAnalysis\Analyser\AnalyserInterface;
use PhpPdgAnalysis\Analyser\FunctionCounts;
use PhpPdgAnalysis\Analyser\LibraryInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command {
	const LIBRARY_ROOT = 'C:\Users\mwijngaard\Documents\Projects\_verification';

	private $cacheFile;
	/** @var AnalyserInterface[] */
	private $analysers;

	public function __construct($cacheFile) {
		$this->cacheFile = $cacheFile;
		$this->analysers = [
			"libraryInfo" => new LibraryInfo(),
			"functionCounts" => new FunctionCounts(),
			"problematicFeatures" => null,
		];
		parent::__construct("update");
	}

	protected function configure() {
		$this
			->setDescription("Update analysis cache")
			->addOption(
				"analyser",
				"a",
				InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
				"Which analysers should be run (default all)",
				array_keys($this->analysers)
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$analyserNames = $input->getOption("analyser");
		foreach ($analyserNames as $analyserName) {
			if (!isset($this->analysers[$analyserName])) {
				throw new \RuntimeException("No such analyser `$analyserName`");
			}
		}
		try {
			$cache = json_decode(file_get_contents($this->cacheFile), true);
		} catch (\Exception $e) {
			$cache = [];
		}

		foreach (new \DirectoryIterator(self::LIBRARY_ROOT) as $fileInfo) {
			if ($fileInfo->isDir() && !$fileInfo->isDot()) {
				$fullPath = (string) $fileInfo;
				$pathCache = isset($cache[$fullPath]) ? $cache[$fullPath] : [];
				foreach ($analyserNames as $analyserName) {
					$analysisResults = $this->analysers[$analyserName]->analyse($fileInfo);
					foreach ($analysisResults as $key => $value) {
						$pathCache[$key] = $value;
					}
				}
				$cache[$fullPath] = $pathCache;
				file_put_contents($this->cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
			}
		}
	}
}