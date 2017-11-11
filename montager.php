<?php

################################
#	Movie Montager aka ScreenShot php code by
#	kieron.welman@gmail.com
################################

Class ScreenShotsObj {

	function create($newFilePath, $nFramesIn, $screenshot_dir){
	    // error checking to see if file is indeed a video file before continueing
	    $mime = mime_content_type($newFilePath);
	    if(!strstr($mime, "video/")){
	        echo "\r\n screenShotK($newFilePath) is not a video file :s \r\n$ext\r\n";
	        exit;
	    }

	    // where avconv is located, such as /usr/sbin/avconv can be changed to ffmpeg if installed
	    $ffmpeg = 'avconv';
	     
	    // the input video file location
	    $video  = $newFilePath;
	    
	    // screenshot resolutions
	    $res = "1280:720";

	    //get the duration
	    $cmd = "$ffmpeg -i $video 2>&1";
	    if (preg_match('/Duration: ((\d+):(\d+):(\d+))/s', `$cmd`, $time)) {
	        $total = ($time[2] * 3600) + ($time[3] * 60) + $time[4];
	        $time = round($total - 1);
	    }

	    // debuging
	    echo "\r\n" . $time . "\r\n -- Total Time" . "\r\n";

	    //$nFrames = 16; // number of frames/screenshots to make
	    $nFrames = $nFramesIn; // number of frames/screenshots to make
	    $split = $time/($nFrames + 2); // deviding with 2+ to chop off the first and last frame later
	    $total = "";
	    $arrT = array(); // timecodes to where to split are places in this array

	    // error checking for time duration
	    if ($time <= 0){
	        echo "time = " . $time . "  Problem getting video duration";
	        // maybe try getting duration using another way
	        exit;
	    }

	    // split the duration into $nFrames timecode blocks +2
	    for ($j = 0; $j < ($nFrames + 2); $j++){
	        $total += $split;
	        array_push($arrT, $total);
	    }

	    // chopping off first frame and last frame to avoid intro titles and credits, count($arrT) should now = $nFrames
	    array_shift($arrT);
	    array_pop($arrT);

	    // create $nFrames of screenshots
	    for ($i = 0; $i < $nFrames; $i++){
	        $splice = round($arrT[$i]); //rounding (to try prevent any confusion with floating numbers to seconds)
	        $outN = $i + 1; // screenshot appended number (so output screenshot files start at 1 not 0)

	        // main screenshot cmd, -ss is timecode to slice (some various other filter stuff)
	        $cmd = "$ffmpeg -ss $splice -i $video -filter:v yadif -an -t 00:00:01 -r 1 -y -vf 'scale=$res' '$screenshot_dir/Screenshot-0$outN.jpg' 2>&1";

	        $return = `$cmd`;
	        //echo "return: " . $return . "\r\n";
	    }

	   	echo "\r\n ScreenShotsObj create($newFilePath, $nFramesIn, $screenshot_dir) Finished \r\n";
	    
	    return $return;
	}

	// Montaging Screenshots
	function montage($tile, $scrDir){
	    // montage bin location
	    $montage = "/usr/bin/montage";
	    $mode = "concatenate";
	    $cmd = "$montage -mode $mode -tile $tile $scrDir" . "Screenshot-0* $scrDir". "Screenshot.jpg";
	    $return = `$cmd`;

	    echo "\r\n ScreenShotsObj montage($tile, $scrDir) Finished \r\n";
	    
	    return $return;
	}

	// Resizing Montaged final image
	function mResize($resize, $scrDir){
	    // mogrify bin location
	    $mogrify = "/usr/bin/mogrify";
	    $cmd = "$mogrify -resize $resize $scrDir" . "Screenshot.jpg";
	    $return = `$cmd`;

	    echo "\r\n ScreenShotsObj mResize($resize, $scrDir) Finished \r\n";

	    return $return;
	}


}
