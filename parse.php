<?php

//----------------------------------------------------------------------------------------
function reference_to_ris($reference)
{
	$field_to_ris_key = array(
		'title' 	=> 'TI',
		'journal' 	=> 'JO',
		'book' 		=> 'T2',
		'issn' 		=> 'SN',
		'volume' 	=> 'VL',
		'issue' 	=> 'IS',
		'spage' 	=> 'SP',
		'epage' 	=> 'EP',
		'year' 		=> 'Y1',
		'date'		=> 'PY',
		'abstract'	=> 'N2',
		'url'		=> 'UR',
		'pdf'		=> 'L1',
		'doi'		=> 'DO',
		'notes'		=> 'N1',
		'publisher'	=> 'PB',
		'publoc'	=> 'PP',
		);
		
	$ris = '';
	
	switch ($reference->genre)
	{
		case 'article':
			$ris .= "TY  - JOUR\n";
			break;

		case 'chapter':
			$ris .= "TY  - CHAP\n";
			break;

		case 'book':
			$ris .= "TY  - BOOK\n";
			break;

		default:
			$ris .= "TY  - GEN\n";
			break;
	}
	
	// Need journal to be output early as some pasring routines that egnerate BibJson
	// assume journal alreday defined by the time we read pages, etc.
	if (isset($reference->journal))
	{
		$ris .= 'JO  - ' . $reference->journal . "\n";
	}

	foreach ($reference as $k => $v)
	{
		switch ($k)
		{
			// eat this
			case 'journal':
				break;
				
			case 'authors':
				foreach ($v as $a)
				{
					if ($a != '')
					{
						$a = str_replace('*', '', $a);
						$a = trim(preg_replace('/\s\s+/u', ' ', $a));						
						$ris .= "AU  - " . $a ."\n";
					}
				}
				break;
				
			case 'editors':
				foreach ($v as $a)
				{
					if ($a != '')
					{
						$ris .= "ED  - " . $a ."\n";
					}
				}
				break;				
				
			case 'date':
				if (preg_match("/^(?<year>[0-9]{4})\-(?<month>[0-9]{2})\-(?<day>[0-9]{2})$/", $v, $matches))
				{
					//print_r($matches);
					$ris .= "PY  - " . $matches['year'] . "/" . $matches['month'] . "/" . $matches['day']  . "/" . "\n";
					$ris .= "Y1  - " . $matches['year'] . "\n";
				}
				else
				{
					$ris .= "Y1  - " . $v . "\n";
				}		
				break;
				
				
			default:
				if ($v != '')
				{
					if (isset($field_to_ris_key[$k]))
					{
						$ris .= $field_to_ris_key[$k] . "  - " . $v . "\n";
					}
				}
				break;
		}
	}
	
	$ris .= "ER  - \n";
	$ris .= "\n";
	
	return $ris;
}

$xml = file_get_contents('marc.xml');

$dom= new DOMDocument;
$dom->loadXML($xml);
$xpath = new DOMXPath($dom);


$count = 1;

$xpath_query = '//catalog/marc';
$nodeCollection = $xpath->query ($xpath_query);
foreach($nodeCollection as $node)
{

    $reference = new stdclass;
    
    foreach ($xpath->query ('marcentry[@tag="260"]', $node) as $n)
    {
    	$reference->title = trim($n->firstChild->nodeValue);
    }
    
    foreach ($xpath->query ('marcentry[@tag="245"]', $node) as $n)
    {
    	$reference->authors[] = trim($n->firstChild->nodeValue);
    }
    
    
    // In: Notes from the Royal Botanic Garden Edinburgh ;--1948 ;--v.20(n.98) 93-105
    // In: Notes from the Royal Botanic Garden Edinburgh ;--1979 ;--v.37 (2) 355-368
    // In: Notes from the Royal Botanic Garden Edinburgh ;--1979 ;--v.37 (2) 355-368
    // In: Notes from the Royal Botanic Garden Edinburgh--1988 ;--v.45 (2) 327-335
    // In: Notes from the Royal Botanic Garden Edinburgh ; 1931 ; v.16 (n.79) 222
    
    $pattern = '/(?<journal>Notes from the Royal Botanic Garden Edinburgh)[\s|;|-]+(?<year>[0-9]{4}(-[0-9]{4})?)[\s|;|-]+[v|V].\s*(?<volume>\d+)(\s*\((?<issue>.*)\))?\s+(?<spage>\d+)(-(?<epage>\d+))?/';
        
    
    foreach ($xpath->query ('marcentry', $node) as $n)
    {
    	// echo $n->firstChild->nodeValue . "\n";
    	if (preg_match($pattern, $n->firstChild->nodeValue, $m))
    	{
    		// print_r($m);
    		
    		$reference->journal = $m['journal'];
    		$reference->issn 	= '0080-4274';
    		$reference->volume 	= $m['volume'];
    		$reference->issue 	= $m['issue'];
    		$reference->issue = str_replace('n.', '', $reference->issue);
    		
    		
    		$reference->spage 	= $m['spage'];
    		
    		if ($m['epage'] != '')
    		{
    			$reference->epage 	= $m['epage'];
    		}
    		
    		$reference->year 	= $m['year'];
    	
    	}
    }
 
    //print_r($reference);
    
    if (!isset($reference->journal))
    {
		print_r($reference);    
    
    	echo "*** Bugger ***\n";
    	exit();
    }
    
	echo reference_to_ris($reference);
    
    
}





/*

<catalog>
		<marc>
			<marcentry tag="100" label="$&lt;cat_usmarc_100v1&gt;" ind="  ">
				65618
			</marcentry>
			<marcentry tag="245" label="$&lt;cat_usmarc_245v1&gt;" ind="  ">
				Anthony, John
			</marcentry>
			<marcentry tag="260" label="$&lt;cat_usmarc_260v1&gt;" ind="  ">
				On Vaccinium donianum Wight
			</marcentry>
			<marcentry tag="300" label="$&lt;cat_usmarc_300v1&gt;" ind="  ">
				1933
			</marcentry>
			<marcentry tag="440" label="$&lt;cat_usmarc_440at&gt;" ind="  ">
				(4p 3 tables)
			</marcentry>
			<marcentry tag="651" label="$&lt;cat_usmarc_651v1&gt;" ind="  ">
				In: Notes from the Royal Botanic Garden Edinburgh ;--1933 ;--v.18 (n.86) 13-16
			</marcentry>
			<marcentry tag="651" label="$&lt;cat_usmarc_651v1&gt;" ind="  ">
				China
			</marcentry>
			<marcentry tag="691" label="$&lt;cat_usmarc_691v1&gt;" ind="  ">
				India
			</marcentry>
			<marcentry tag="691" label="$&lt;cat_usmarc_691v1&gt;" ind="  ">
				Ericaceae
			</marcentry>
		</marc>
		<call>
			<item>
				<copynumber>
					1
				</copynumber>
				<itemid>
					60995-1001
				</itemid>
				<location>
					ON-SHELF
				</location>
			</item>
		</call>
	</catalog>

*/

?>
