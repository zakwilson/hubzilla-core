<?php

namespace Zotlabs\Thumbs;


class Video {

	function MatchDefault($type) {
		return(($type === 'video') ? true : false );
	}

	function Thumb($attach,$preview_style,$height = 300, $width = 300) {

		$photo = false;

		$t = explode('/',$attach['filetype']);
		if($t[1])
			$extension = '.' . $t[1];
		else
			return; 


		$file = dbunescbin($attach['content']);
		$tmpfile = $file . $extension;
		$outfile = $file . '.jpg';

		$istream = fopen($file,'rb');
		$ostream = fopen($tmpfile,'wb');
		if($istream && $ostream) {
			pipe_streams($istream,$ostream);
			fclose($istream);
			fclose($ostream);
		}

		$imagick_path = get_config('system','imagick_convert_path');
		if($imagick_path && @file_exists($imagick_path)) {
			$cmd = $imagick_path . ' ' . escapeshellarg(PROJECT_BASE . '/' . $tmpfile . '[0]') . ' -thumbnail ' . $width . 'x' . $height . ' ' . escapeshellarg(PROJECT_BASE . '/' . $outfile);
			//  logger('imagick thumbnail command: ' . $cmd);

			exec($cmd);

			if(! file_exists($outfile)) {
				logger('imagick scale failed.');
			}
			else {
				@rename($outfile,$file . '.thumb');
			}
		}
			
		@unlink($tmpfile);
	}
}

