<?php
/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
* 
* This program is free software; you can redistribute it and/or 
* modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation; either version 2 
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
* GNU General Public License for more details: 
* http://www.gnu.org/licenses/gpl.html
*
*/
 
 
 
class SimpleImage {
   
   var $image;
   var $image_type;
   
   function load($filename) {
      $image_info = getimagesize($filename);

		// This part added to account for out of memory issue
		// from http://www.daniweb.com/web-development/php/threads/203890 
		// It also is possible to override the php.ini file to change max memory use and using php.cgi on pair.com: http://drupal.org/node/145883
		// also see this for calculating memory sizes: http://www.dotsamazing.com/en/labs/phpmemorylimit
		
		/* debug
		$memoryNeeded = round(($image_info[0] * $image_info[1] *
		$image_info['bits'] * $image_info['channels'] / 8 + Pow(2, 16)) * 1.65);
		echo("Memory needed: " . $memoryNeeded . " bytes. <br/>");
		echo("Memory limit: " . ini_get('memory_limit') . " bytes. <br/>");
		setMemoryForImage($filename);		
		echo("Memory expanded 1: " . ini_get('memory_limit') . " bytes. <br/>");
		*/
		
		// According to http://www.dotsamazing.com/en/labs/phpmemorylimit, this should accommodate images up to 6000 x 6000 pixels (36 megapixels or ~100 MB RGB)
		
		ini_set('memory_limit','256M');
		echo("Memory limit: " . ini_get('memory_limit') . "<br/>");

      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
         $this->image = imagecreatefrompng($filename);
      }
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image,$filename);         
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image,$filename);
      }   
      if( $permissions != null) {
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image);         
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image);
      }   
   }
   function getWidth() {
      return imagesx($this->image);
   }
   function getHeight() {
      return imagesy($this->image);
   }
   function resizeToHeight($height) {
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100; 
      $this->resize($width,$height);
   }
   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;   
   }      
}

function setMemoryForImage($filename){
	
	// This function is added from http://php.net/manual/en/function.imagecreatefromjpeg.php#64155
	// however, it does not seem to be working so i am simply doing a hard increase of memory size in the load function with set_ini
	
	$imageInfo = getimagesize($filename);
	$MB = 1048576;  // number of bytes in 1M
	$K64 = 65536;    // number of bytes in 64K
	$TWEAKFACTOR = 1.5;  // Or whatever works for you
	$memoryNeeded = round( ( $imageInfo[0] * $imageInfo[1]
										   * $imageInfo['bits']
										   * $imageInfo['channels'] / 8
							 + $K64
						   ) * $TWEAKFACTOR
						 );
	$memoryLimit = 8 * $MB;
	if (function_exists('memory_get_usage') && 
		memory_get_usage() + $memoryNeeded > $memoryLimit) 
	{
		$newLimit = $memoryLimitMB + ceil( ( memory_get_usage()
											+ $memoryNeeded
											- $memoryLimit
											) / $MB
										);
		
		ini_set( 'memory_limit', $newLimit . 'M' );
		return true;
		
	} else {
	
		return false;
	}
}
?>