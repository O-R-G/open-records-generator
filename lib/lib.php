<?
/* miscellaneous functions */

function slug($name = "untitled")
{	
	// replace non-alphanumerics at the beginning and end of the 
	// string with nothing
	$pattern = '/(\A[^\p{L}\p{N}]+|[^\p{L}\p{N}]+\z)/u';
	$replace = '';
	$tmp = preg_replace($pattern, $replace, $name);
	
	// replace all non-alphanumerics with hyphens
	$pattern = '/[^\p{L}\p{N}]+/u';
	$replace = '-';
	$tmp = preg_replace($pattern, $replace, $tmp);
	
	// make string url lowercase (in a unicode-safe way)
	$tmp = mb_convert_case($tmp, MB_CASE_LOWER, "UTF-8");

	// transliterate utf-8 characters to plain ascii
	setlocale(LC_ALL, 'de_DE');	// german 
	// setlocale(LC_ALL, 'en_GB');	// english
	$tmp = iconv('UTF-8', 'ASCII//TRANSLIT', $tmp);

	// make url safe
	$tmp = urlencode($tmp);
	
	return $tmp;
}

// return a string of random characters [a-z, 0-9]
// of length $len
function rand_str($len=4)
{
	$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
	$max = strlen($chars)-1;
	$s = "";
	for($i = 0; $i < $len; $i++)
	{
		$c = substr($chars, rand(0, $max), 1);
		$s.= $c;
	}
	return $s;
}

// for our purposes, $alt is assumed to be the id of the object
// this url is meant to reference
function valid_url($u, $alt, $excludes)
{
	// array_search returns the position (index) of $u in 
	// $excludes, or false if not present in the array.
	// therefore, strict compare to false
	if(empty($u) || array_search($u, $excludes) !== false)
	{
		$url = $alt;
		while(array_search($url, $excludes) !== false)
			$url = rand_str();
	}
	else
		$url = $u;
	
	return $url;
}

// why do i need two of these? 
// which would be better to keep? probably the second one.
// maybe the variables should be passed instead of called on globally
function m_pad($m)
{
	global $m_pad;
	return str_pad($m, $m_pad, "0", STR_PAD_LEFT);
}

function m_url($m)
{
	global $media_path;
	return $media_path.m_pad($m['id']).".".$m['type'];
}

function m_root($m)
{
	global $media_root;
	return $media_root.m_pad($m['id']).".".$m['type'];
}

// this has not been tested
function resize($src, $dest, $scale)
{
	include('lib/SimpleImage.php');
	$si = new SimpleImage();
	$si->load($src);
	$si->scale($scale);
	$si->save($dest);
}



function md2html($md)
{
	if(!$md)
		return "";
	$p = "/usr/home/lilyhealey/public_html/dev.lilyhealey.co.uk/lib/";
	$f = "out.txt";
	file_put_contents($p.$f, $md);
	exec($p."Markdown.pl ".$p.$f, $html);
	unlink($p.$f);
	return implode("\n", $html);
}

function html2md($html)
{
	if(!$html)
		return "";
	$p = "/usr/home/lilyhealey/public_html/dev.lilyhealey.co.uk/lib/";
	$f = "out.txt";
	file_put_contents($p.$f, $html);
	exec($p."html2text.py ".$p.$f, $md);
	unlink($p.$f);
	return implode("\n", $md);
}

function process_media($toid)
{
	global $mm;
	global $rr;
	global $resize;
	global $resize_root;
	global $resize_scale;
	global $media_root;
	
	$m_rows = $mm->num_rows();
	$m_old = $m_rows;
	foreach($_FILES["uploads"]["error"] as $key => $error)
	{
		if($error == UPLOAD_ERR_OK)
		{
			$tmp_name = $_FILES["uploads"]["tmp_name"][$key];
			$m_name = $_FILES["uploads"]["name"][$key];
			$m_type = strtolower(end(explode(".", $m_name)));
			$m_file = m_pad(++$m_rows).".".$m_type;
			
			$m_dest = $resize ? $resize_root : $media_root;
			$m_dest.= $m_file;
			
			if(move_uploaded_file($tmp_name, $m_dest))
			{
				if($resize)
					resize($m_dest, $media_root.$m_file, $resize_scale);
				
				// add to db's image list
				$m_arr["type"] = "'".$m_type."'";
				$m_arr["object"] = "'".$toid."'";
				$m_arr["caption"] = "'".$rr->captions[$key+count($rr->medias)]."'";
				$mm->insert($m_arr);
			}
			else
				$m_rows--;
		}
	}
	return $m_old < $m_rows;
}
?>
