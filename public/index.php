<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Colors\RandomColor;
use Nette\SmartObject;
use Nette\Utils\Finder;
use Tracy\Debugger;

Debugger::enable(Debugger::DEVELOPMENT, __DIR__. '/../log');

final class Point
{

	use SmartObject;

	public $latitude, $longitude;

	public function __construct(float $latitude, float $longitude)
	{
		$this->latitude = $latitude;
		$this->longitude = $longitude;
	}

	public function compare(Point $p): bool
	{
		return $p->latitude === $this->latitude && $p->longitude === $this->longitude;
	}

}

$lines = [];

$finder = Finder::findFiles('*.gpx')->in(__DIR__ . '/../gpx');
$files = iterator_to_array($finder);
ksort($files);

$placemarkTpl = file_get_contents(__DIR__ . '/../templates/placemark.tpl');

$j = 0;

/**
 * @var string $path
 * @var \SplFileInfo $file
 */
foreach ($files as $key => $file) {

	$xml = new SimpleXMLElement(file_get_contents($key));
	$previous = null;
	$coordinates = [];

	$j++;
	$i = 0;
	$segmentKey = 0;
	foreach ($xml->trk->trkseg as $segment) {

		foreach ($segment->trkpt as $point) {

			$miss = $_GET['miss'] ?? $argv[1] ?? 10;

			if ($i++ % $miss !== 0) {
				continue;
			}

			$point = new Point((float) $point['lat'], (float) $point['lon']);
			if ($previous && $point->compare($previous)) {
				continue;
			}

			$coordinates[] = sprintf('%F,%F,0', $point->longitude, $point->latitude);

			$previous = $point;
		}

		// convert hex color to plain hex BGR
		$color = array_reverse(str_split(substr(RandomColor::one(['hue' => 'red']), 1), 2));
		$lines[$j] = sprintf($placemarkTpl, $file->getBasename(), vsprintf('#00%s%s%s', $color), implode(' ', $coordinates));
	}
	$segmentKey = 0;
}

if (!$lines) {
	header('HTTP/1.1 404 Not Found');
	echo 'No input data to work with';
	return 1;
}

$template = file_get_contents(__DIR__ . '/../templates/template.kml');

$content = sprintf($template, implode("\n\n", $lines));

// file_put_contents(__DIR__ . '/../templates/result.kml', $content);

header('content-type: application/vnd.google-earth.kml+xml');
header('content-disposition: attachment; filename=output.kml');
header('content-length: ' . strlen($content));

echo $content;
return 0;
