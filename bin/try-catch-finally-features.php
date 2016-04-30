<?php

require(__DIR__ . '/../vendor/autoload.php');

$lib_root = "C:\\Users\\mwijngaard\\Documents\\Projects\\_verification";
$parser = (new \PhpParser\ParserFactory())->create(\PhpParser\ParserFactory::ONLY_PHP5);
$traverser = new \PhpParser\NodeTraverser();
$traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
$countingVisitor = new \PhpPdgAnalysis\ExceptionCountingVisitor();
$traverser->addVisitor($countingVisitor);
$cachefile = __DIR__ . '/cache/try-catch-finally-features.json';
$cache = file_exists($cachefile) ? json_decode(file_get_contents($cachefile), true) : [];

foreach (new DirectoryIterator($lib_root) as $f1) {
	if ($f1->isDir() && !$f1->isDot()) {
		echo "entering $f1\n";
		if (isset($cache[(string) $f1])) {
			echo sprintf("in cache: ");
			$cacheEntry = $cache[(string) $f1];
		} else {
			$filect = 0;
			$throws = 0;
			$filesWithThrowCt = 0;
			$errorFileCt = 0;
			$filesWithTryCatch = 0;
			$totalTrys = 0;
			$trysWithCatch = 0;
			$totalCatches = 0;
			$trysWithFinally = 0;
			$throwsInTrys = 0;
			echo "parsing files: ";
			foreach (new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$lib_root\\$f1")), "/.*\.php$/i") as $f2) {
				$filect++;
				try {
					$parsed = $parser->parse(file_get_contents($f2));
					$traverser->traverse($parsed);
					if ($countingVisitor->tryCount > 0) {
						$filesWithTryCatch++;
					}
					$totalTrys += $countingVisitor->tryCount;
					$trysWithCatch += $countingVisitor->tryWithCatchCount;
					$totalCatches += $countingVisitor->totalCatchCount;
					$trysWithFinally += $countingVisitor->tryWithfinallyCount;
					$throws += $countingVisitor->throwCount;
					if ($countingVisitor->throwCount > 0) {
						$filesWithThrowCt++;
					}
					$throwsInTrys += $countingVisitor->throwInTryCount;
					echo ".";
				} catch (Exception $e) {
					$errorFileCt++;
					echo "E";
				}
			}
			echo "\n";
			$cacheEntry = [
				'filect' => $filect,
				'errorFileCt' => $errorFileCt,
				'filesWithTryCatch' => $filesWithTryCatch,
				'totalTrys' => $totalTrys,
				'trysWithCatch' => $trysWithCatch,
				'totalCatches' => $totalCatches,
				'trysWithFinally' => $trysWithFinally,
				'filesWithThrowCt' => $filesWithThrowCt,
				'throws' => $throws,
				'throwsInTrys' => $throwsInTrys
			];
			$cache[(string) $f1] = $cacheEntry;
			$cachedir = dirname($cachefile);
			if (!is_dir($cachedir)) {
				mkdir($cachedir, 0777, true);
			}
			file_put_contents($cachefile, json_encode($cache, JSON_PRETTY_PRINT));
			echo "done: ";
		}
		echo sprintf("%d files, %d files with throws, %d total throws, %d files with try's, %d total try's, %d try's with catches, %d total catches, %d try's with finally, %d throws in try's\n", $cacheEntry['filesWithThrowCt'], $cacheEntry['throws'], $cacheEntry['filect'], $cacheEntry['filesWithTryCatch'], $cacheEntry['totalTrys'], $cacheEntry['trysWithCatch'], $cacheEntry['totalCatches'], $cacheEntry['trysWithFinally'], $cacheEntry['throwsInTrys']);
	}
}