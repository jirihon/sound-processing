#!/usr/bin/php
<?php

$dates = [
	'0010' => '180629',
	'0200' => '180630',
	'0300' => '180701',
	'0410' => '180702',
	'0940' => '180703',
	'1030' => '180804',
];

$flac_dir = 'master';

$flac_files = scandir($flac_dir);

foreach ($flac_files as $flac_file) {
	if (preg_match('#^(\d+) (.*)\.flac$#', $flac_file, $m)) {
		
		list(, $song_number, $song_name) = $m;
		$song_date = '';
		
		foreach ($dates as $date_number => $date) {
			if (strcmp($song_number, $date_number) >= 0) {
				$song_date = $date;
			}
		}
		$metadata[] = [$flac_file, $song_name, $song_date];
	}
}


/**
 * Analyze volume statistics
 */
$volumes = [];
$ranges = [];
$extreme = 0;

foreach ($metadata as $id => list($file, $title, $date))
{
	$out = shell_exec(sprintf('ffmpeg -i "%s/%s" -af volumedetect -f null /dev/null 2>&1', $flac_dir, $file));
	$item = [];
	preg_match('#mean_volume: ([\d\.\-]+)#', $out, $match);
	$item['mean'] = $match[1];
	preg_match('#max_volume: ([\d\.\-]+)#', $out, $match);
	$item['max'] = $match[1];
	$volumes[$file] = $item;
	
	$range = $item['max'] - $item['mean'];
	$ranges[$file] = $range;
	
	echo $id.' '.$item['mean'].' '.$item['max'].' '.$range.' '.$file."\n";
	
	
	if ($range > $extreme)
	{
		$extreme = $range;
		$efile = $file;
	}
}

echo 'Extreme: '.$extreme.' dB, '.$efile."\n";

asort($ranges);

foreach ($ranges as $file => $range) {
	echo sprintf("%g %s\n", $range, $file);
}


foreach ($metadata as $id => list($file, $title, $date))
{
	$track = $id + 1;
	$fname = preg_replace('#[^a-zA-Z0-9]+#', '_', $title);
	
	$gain = -($volumes[$file]['mean'] + $extreme);
	$cmd = sprintf('ffmpeg -i "%s/%s" -af "volume=%sdB" -c:a libmp3lame -q:a 1 ', $flac_dir, $file, $gain).
	'-metadata track="'.$track.'" '.
	'-metadata title="'.$title.'" '.
	'-metadata album="22. FSM" '.
	'-metadata genre="97" '.
	'-metadata artist="Schola opavske mladeze" '.
	'-metadata date="2018" '.
	sprintf('mp3/%03d_%s_%s.mp3 2>/dev/null', $track, $fname, $date);
	echo $cmd."\n";
	shell_exec($cmd);
}
