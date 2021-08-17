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

    // force url to all ASCII
    $tmp = remove_accents($tmp);

	// make url safe
    // this is a failsafe in case remove_accents misses something
    // urlencode() is for query strings, ' ' -> '+'
    // rawurlencode() is for urls, ' ' -> '%20'
	// $tmp = urlencode($tmp);
	$tmp = rawurlencode($tmp);

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
    if( $alt < 10 )
        $alt = '000' . $alt;
    else if($alt < 100)
        $alt = '00' . $alt;
    else if($alt < 1000)
        $alt = '0' . $alt;
    $url = $u . '-' . $alt;
    while(array_search($url, $excludes) !== false)
        $url = $u . '-' . rand(1000, 9999);

    return $url;
}
function validate_url($u, $excludes)
{
    if(empty($u) || array_search($u, $excludes) !== false)
        return false;
    else
        return true;
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
            $temp = explode(".", $m_name);
			$m_type = strtolower(end($temp));

			// add to db's image list
			$m_arr["type"] = "'".$m_type."'";
			$m_arr["object"] = "'".$toid."'";
            
            if($rr->captions != null){
                $count_media = $rr->medias == null ? 0 : count($rr->medias);
                if( isset($rr->captions[$key+$count_media]) )
                    $m_arr["caption"] = "'".$rr->captions[$key+$count_media]."'";
                else
                    $m_arr["caption"] = "''";
            }
            else
                $m_arr["caption"] = "''";
			
			$insert_id = $mm->insert($m_arr);
			$m_rows++;

			$m_file = m_pad($insert_id).".".$m_type;
			$m_dest = $resize ? $resize_root : $media_root;
			$m_dest.= $m_file;

			if(move_uploaded_file($tmp_name, $m_dest)) {
				if($resize)
					resize($m_dest, $media_root.$m_file, $resize_scale);
			}
			else {
				$m_rows--;
				$mm->deactivate($insert_id);
			}
		}
	}
	return $m_old < $m_rows;
}

// remove_accents adapted from wordpress core
// converts all accent characters to ASCII characters.
// if there are no accent characters, then the string given is just returned.
// https://core.trac.wordpress.org/browser/tags/3.9.1/src/wp-includes/formatting.php#L682
// https://stackoverflow.com/questions/1017599/how-do-i-remove-accents-from-characters-in-a-php-string
// called in slug() to force all characters in url slug to ascii before write to db

/*
    character conversion reference 

    À => A
    Á => A
    Â => A
    Ã => A
    Ä => A
    Å => A
    Ç => C
    È => E
    É => E
    Ê => E
    Ë => E
    Ì => I
    Í => I
    Î => I
    Ï => I
    Ñ => N
    Ò => O
    Ó => O
    Ô => O
    Õ => O
    Ö => O
    Ù => U
    Ú => U
    Û => U
    Ü => U
    Ý => Y
    ß => s
    à => a
    á => a
    â => a
    ã => a
    ä => a
    å => a
    ç => c
    è => e
    é => e
    ê => e
    ë => e
    ì => i
    í => i
    î => i
    ï => i
    ñ => n
    ò => o
    ó => o
    ô => o
    õ => o
    ö => o
    ù => u
    ú => u
    û => u
    ü => u
    ý => y
    ÿ => y
    Ā => A
    ā => a
    Ă => A
    ă => a
    Ą => A
    ą => a
    Ć => C
    ć => c
    Ĉ => C
    ĉ => c
    Ċ => C
    ċ => c
    Č => C
    č => c
    Ď => D
    ď => d
    Đ => D
    đ => d
    Ē => E
    ē => e
    Ĕ => E
    ĕ => e
    Ė => E
    ė => e
    Ę => E
    ę => e
    Ě => E
    ě => e
    Ĝ => G
    ĝ => g
    Ğ => G
    ğ => g
    Ġ => G
    ġ => g
    Ģ => G
    ģ => g
    Ĥ => H
    ĥ => h
    Ħ => H
    ħ => h
    Ĩ => I
    ĩ => i
    Ī => I
    ī => i
    Ĭ => I
    ĭ => i
    Į => I
    į => i
    İ => I
    ı => i
    Ĳ => IJ
    ĳ => ij
    Ĵ => J
    ĵ => j
    Ķ => K
    ķ => k
    ĸ => k
    Ĺ => L
    ĺ => l
    Ļ => L
    ļ => l
    Ľ => L
    ľ => l
    Ŀ => L
    ŀ => l
    Ł => L
    ł => l
    Ń => N
    ń => n
    Ņ => N
    ņ => n
    Ň => N
    ň => n
    ŉ => N
    Ŋ => n
    ŋ => N
    Ō => O
    ō => o
    Ŏ => O
    ŏ => o
    Ő => O
    ő => o
    Œ => OE
    œ => oe
    Ŕ => R
    ŕ => r
    Ŗ => R
    ŗ => r
    Ř => R
    ř => r
    Ś => S
    ś => s
    Ŝ => S
    ŝ => s
    Ş => S
    ş => s
    Š => S
    š => s
    Ţ => T
    ţ => t
    Ť => T
    ť => t
    Ŧ => T
    ŧ => t
    Ũ => U
    ũ => u
    Ū => U
    ū => u
    Ŭ => U
    ŭ => u
    Ů => U
    ů => u
    Ű => U
    ű => u
    Ų => U
    ų => u
    Ŵ => W
    ŵ => w
    Ŷ => Y
    ŷ => y
    Ÿ => Y
    Ź => Z
    ź => z
    Ż => Z
    ż => z
    Ž => Z
    ž => z
    ſ => s
    
*/

function remove_accents($string) {
    if ( !preg_match('/[\x80-\xff]/', $string) )
        return $string;

    $chars = array(
    // Decompositions for Latin-1 Supplement
    chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
    chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
    chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
    chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
    chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
    chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
    chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
    chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
    chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
    chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
    chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
    chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
    chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
    chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
    chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
    chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
    chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
    chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
    chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
    chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
    chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
    chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
    chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
    chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
    chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
    chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
    chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
    chr(195).chr(191) => 'y',
    // Decompositions for Latin Extended-A
    chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
    chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
    chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
    chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
    chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
    chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
    chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
    chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
    chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
    chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
    chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
    chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
    chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
    chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
    chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
    chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
    chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
    chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
    chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
    chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
    chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
    chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
    chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
    chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
    chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
    chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
    chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
    chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
    chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
    chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
    chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
    chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
    chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
    chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
    chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
    chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
    chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
    chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
    chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
    chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
    chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
    chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
    chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
    chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
    chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
    chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
    chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
    chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
    chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
    chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
    chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
    chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
    chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
    chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
    chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
    chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
    chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
    chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
    chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
    chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
    chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
    chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
    chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
    chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
    );

    $string = strtr($string, $chars);

    return $string;
}
?>

