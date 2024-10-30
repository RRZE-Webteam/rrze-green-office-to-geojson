<?php
if (!isset($argv[1])) {
	echo "Source not defined! Usage: $argv[0] source (destination)\n";
	exit;
} else if (!$src = file_get_contents($argv[1])) {
	echo "Source not existing or reachable!\n";
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';
$data = json_decode($src);
$features = [];
$catColor = [
	'Fahrrad-Reparaturstation' => '#d63e2a',
	'Fahrradwerkstatt' => '#a23336',
	'VAG-Rad-Station' => '#f69730',
	'Duschmoglichkeit' => '#72af26',
	'Mobilpunkt' => '#728224',
	'E-Ladesaule' => '#38aadd',
	'Car-Sharing' => '#d252b9',
	'kostenfreie CityLinie 299' => '#5b396b',
	'Bushaltestelle' => '#0067a3',
	'Bahnstation' => '#0067a3',
	'default' => 'default',
];
$catIcon = [
	'Fahrrad-Reparaturstation' => 'wrench',
	'Fahrradwerkstatt' => 'magic',
	'VAG-Rad-Station' => 'bicycle',
	'Duschmoglichkeit' => 'shower',
	'Mobilpunkt' => 'rocket',
	'E-Ladesaule' => 'plug',
	'Car-Sharing' => 'car',
	'kostenfreie CityLinie 299' => 'bus',
	'Bushaltestelle' => 'bus',
	'Bahnstation' => 'train',
	'default' => 'circle-o',
];

foreach ($data->locations as $item) {
	$markerColor = $catColor[$item->category] ?? $catColor['default'];
	$markerIcon = $catIcon[$item->category] ?? $catIcon['default'];
	$point = new \GeoJson\Geometry\Point([$item->longitude, $item->latitude]);
	$properties = [
		'category' => $item->category,
		'name' => $item->Name,
		'address' => $item->address,
		'popup' => '<h4>' . $item->category . '</h4>' . $item->Name . '<br>' . $item->address,
		'marker-color' => $markerColor,
		'marker-icon' => $markerIcon,
	];
	$feature = new \GeoJson\Feature\Feature($point, $properties);
	$features[] = $feature;
}
$fc = new \GeoJson\Feature\FeatureCollection($features);
$geojson = json_encode($fc->jsonSerialize(), JSON_PRETTY_PRINT);

if (isset($argv[2])) {
	$dest = $argv[2];
	if(file_exists($dest)) {
		$dt = DateTime::createFromFormat('U', filemtime($dest));
		$dt->setTimezone(new DateTimeZone('Europe/Berlin'));
		$ts = $dt->format('Ymd-His');
		$answer = readline($dest . " exists! Overwrite (o) or move existing file to $dest-$ts? (m)?");
		if ($answer === 'm') {
			rename($dest, $dest . '-' . $ts);
		} elseif ($answer === 'o') {
			# nothing to do here
		} else {
			echo "Neither o nor m - bye!\n";
			exit;
		}
	}
	file_put_contents($dest, $geojson);
} else {
	echo $geojson;
}
