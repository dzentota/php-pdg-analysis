<?php

require(__DIR__ . '/../vendor/autoload.php');

$lib_root = "C:\\Users\\mwijngaard\\Documents\\Projects\\_verification";
$parser = (new \PhpParser\ParserFactory())->create(\PhpParser\ParserFactory::ONLY_PHP5);
$traverser = new \PhpParser\NodeTraverser();
$traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
$visitor = new \PhpPdgAnalysis\PdgUnresolvableFeatureCountingVisitor();
$traverser->addVisitor($visitor);
$cachefile = __DIR__ . '/cache/pdg_unresolvable_features.json';
$cache = file_exists($cachefile) ? json_decode(file_get_contents($cachefile), true) : [];

foreach (new DirectoryIterator($lib_root) as $f1) {
	if ($f1->isDir() && !$f1->isDot()) {
		echo "entering $f1\n";
		if (isset($cache[(string) $f1])) {
			echo sprintf("in cache: ");
			$cacheEntry = $cache[(string) $f1];
		} else {
			$filect = 0;
			$errorFileCt = 0;

			$funcCount = 0;

			$funcsWithVarVarCount = 0;
			$varVarCount = 0;

			$funcsWithEvalCount = 0;
			$evalCount = 0;

			$funcsWithPregEvalCount = 0;
			$pregEvalCount = 0;

			$funcsWithRefArrayItemCount = 0;
			$refArrayItemCount = 0;

			$funcsWithGlobalCount = 0;
			$globalCount = 0;

			$funcsWithIncludeCount = 0;
			$includeCount = 0;

			$funcsWithAnyCount = 0;

			echo "parsing files: ";
			foreach (new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$lib_root\\$f1")), "/.*\.php$/i") as $f2) {
				$filect++;
				try {
					$parsed = $parser->parse(file_get_contents($f2));
					$traverser->traverse($parsed);
					$funcCount += $visitor->funcCount;

					$funcsWithVarVarCount += $visitor->funcsWithVarVarCount;
					$varVarCount += $visitor->varVarCount;

					$funcsWithEvalCount += $visitor->funcsWithEvalCount;
					$evalCount += $visitor->evalCount;

					$funcsWithPregEvalCount += $visitor->funcsWithPregEvalCount;
					$pregEvalCount += $visitor->pregEvalCount;

					$funcsWithRefArrayItemCount += $visitor->funcsWithRefArrayItemCount;
					$refArrayItemCount += $visitor->refArrayItemCount;

					$funcsWithGlobalCount += $visitor->funcsWithGlobalCount;
					$globalCount += $visitor->globalCount;

					$funcsWithIncludeCount += $visitor->funcsWithIncludeCount;
					$includeCount += $visitor->includeCount;

					$funcsWithAnyCount += $visitor->funcsWithAnyCount;
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
				'funcCount' => $funcCount,
				'funcsWithVarVarCount' => $funcsWithVarVarCount,
				'varVarCount' => $varVarCount,
				'funcsWithEvalCount' => $funcsWithEvalCount,
				'evalCount' => $evalCount,
				'funcsWithPregEvalCount' => $funcsWithPregEvalCount,
				'pregEvalCount' => $pregEvalCount,
				'funcsWithRefArrayItemCount' => $funcsWithRefArrayItemCount,
				'refArrayItemCount' => $refArrayItemCount,
				'funcsWithGlobalCount' => $funcsWithGlobalCount,
				'globalCount' => $globalCount,
				'funcsWithIncludeCount' => $funcsWithIncludeCount,
				'includeCount' => $includeCount,
				'funcsWithAnyCount' => $funcsWithAnyCount,
			];
			$cache[(string) $f1] = $cacheEntry;
			$cachedir = dirname($cachefile);
			if (!is_dir($cachedir)) {
				mkdir($cachedir, 0777, true);
			}
			file_put_contents($cachefile, json_encode($cache, JSON_PRETTY_PRINT));
			echo "done: ";
		}
		echo sprintf("%d files, %d total funcs, %d funcs with varvars, %d total varvars, %d funcs with evals, %d total evals, %d funcs with preg evals, %d total preg evals, %d funcs with ref array items, %d total ref array items, %d funcs with global, %d total globals, %d funcs with includes, %d total includes, %d funcs with any\n",
			$cacheEntry['filect'],
			$cacheEntry['funcCount'],
			$cacheEntry['funcsWithVarVarCount'],
			$cacheEntry['varVarCount'],
			$cacheEntry['funcsWithEvalCount'],
			$cacheEntry['evalCount'],
			$cacheEntry['funcsWithPregEvalCount'],
			$cacheEntry['pregEvalCount'],
			$cacheEntry['funcsWithRefArrayItemCount'],
			$cacheEntry['refArrayItemCount'],
			$cacheEntry['funcsWithGlobalCount'],
			$cacheEntry['globalCount'],
			$cacheEntry['funcsWithIncludeCount'],
			$cacheEntry['includeCount'],
			$cacheEntry['funcsWithAnyCount']
		);
	}
}