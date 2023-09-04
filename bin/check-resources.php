<?php
declare(strict_types = 1);

function checkDirectory(string $type, array &$messages): int {
	$errors  = 0;
	$classes = glob(__DIR__ . '/../src/Message/' . $type . '/*.php');
	echo count($classes) . ' ' . $type . ' messages found.' . PHP_EOL;
	foreach ($classes as $class) {
		$name = basename($class);
		if (str_starts_with($name, 'Abstract')) {
			continue;
		}
		$name = substr($name, 0, strlen($name) - 4);
		if (isset($messages[$name])) {
			unset($messages[$name]);
		} else {
			echo 'Missing translation for ' . $name . '!' . PHP_EOL;
			$errors++;
		}
	}
	return $errors;
}

$resources = json_decode(file_get_contents(__DIR__ . '/../resources/strings.json'), true);
$messages  = $resources['message'];
echo 'Checking ' . count($messages) . ' message translations...' . PHP_EOL;

$errors  = checkDirectory('Construction', $messages);
$errors += checkDirectory('Party', $messages);
$errors += checkDirectory('Party/Administrator', $messages);
$errors += checkDirectory('Party/Event', $messages);
$errors += checkDirectory('Region', $messages);
$errors += checkDirectory('Region/Event', $messages);
$errors += checkDirectory('Unit', $messages);
$errors += checkDirectory('Unit/Act', $messages);
$errors += checkDirectory('Unit/Apply', $messages);
$errors += checkDirectory('Unit/Cast', $messages);
$errors += checkDirectory('Unit/Operate', $messages);
$errors += checkDirectory('Vessel', $messages);

if (empty($messages)) {
	echo 'No unmatched messages found.' . PHP_EOL;
}
foreach (array_keys($messages) as $name) {
	echo 'Unmatched message: ' . $name . PHP_EOL;
	$errors++;
}
if ($errors) {
	echo $errors . ' errors found.' . PHP_EOL;
}
