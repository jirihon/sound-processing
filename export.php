#!/usr/bin/php
<?php

$flac_dir = 'master';

$flac_files = scandir($flac_dir);

foreach ($flac_files as $flac_file) {
	if (preg_match('#^(\d+) (.*)\.flac$#', $flac_file, $m)) {
		
		list(, $song_number, $song_name) = $m;
		
		$metadata[] = [$flac_file, $song_name];
	}
}


/**
 * Analyze volume statistics
 */
$volumes = [];
$ranges = [];
$extreme = 0;

foreach ($metadata as $id => list($file, $title))
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


foreach ($metadata as $id => list($file, $title))
{
	$track = $id + 1;
	$fname = preg_replace('#[^a-zA-Z0-9]+#', '_', $title);
	
	$gain = -($volumes[$file]['mean'] + $extreme);
	$cmd = sprintf('ffmpeg -i "%s/%s" -af "volume=%sdB" -c:a libmp3lame -q:a 1 ', $flac_dir, $file, $gain).
	'-metadata track="'.$track.'" '.
	'-metadata title="'.$title.'" '.
	'-metadata album="XXX" '.
	'-metadata genre="97" '.
	'-metadata artist="YYY" '.
	'-metadata date="2019" '.
	sprintf('mp3/%02d_%s.mp3 2>/dev/null', $track, $fname);
	echo $cmd."\n";
	shell_exec($cmd);
}
