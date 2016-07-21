<?php

$tracefile = 'C:\Users\mwijngaard\Documents\Projects\_traces\trace.doctrine.xt';
$combinedfile = __DIR__ . '/trace_combined.json';

if (is_file($tracefile) === false) {
	die("Tracefile does not exist");
}

$f = fopen($tracefile, 'r');
while (strpos(($l = fgets($f)), 'TRACE START') !== 0) {}

$starttime = microtime(true);
$i = 0;
$stack = [];
$currentfunc = null;
$calls = [];
$recordct = 0;
while (($row = fgetcsv($f, null, "\t")) !== false) {
	if (isset($row[2])) {
		$recordtype = $row[2];
		switch ($row[2]) {
			case '0':
				$func = $row[5];
				$file = $row[8];
				$line = $row[9];
				if ($currentfunc !== null) {
					$calls[$currentfunc][$func][$file][$line] = isset($calls[$currentfunc][$func][$file][$line]) ? $calls[$currentfunc][$func][$file][$line] + 1 : 1;
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
foreach ($calls as $fromfunc => $tofuncs) {
	foreach ($tofuncs as $files) {
		foreach ($files as $lines) {
			$callpairct++;
		}
	}
	$callpairct += count($tofuncs);
}
file_put_contents($combinedfile, json_encode($calls, JSON_PRETTY_PRINT));
$runtime = microtime(true) - $starttime;
echo "Total: $recordct records, containing $callpairct unique call pairs\n";
echo sprintf("Time: %0.2fs\n", $runtime);