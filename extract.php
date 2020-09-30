<?php

require_once(dirname(__FILE__) . '/ris.php');


//----------------------------------------------------------------------------------------
// Get local file name for PDF
function get_pdf_filename($reference)
{
	$filename = $reference->pdf;
	
	$filename = str_replace('file://', '', $filename);
	$filename = preg_replace('/[#|\?]page=-?\d+$/', '', $filename);
	
	return $filename;
}

//----------------------------------------------------------------------------------------
// Use fragment identifier to specify starting page in PDF
function get_pdf_offset($reference)
{
	$offset = 0;
	
	if (preg_match('/[#|\?]page=(?<offset>-?\d+)$/', $reference->pdf, $m))
	{
		$offset = $m['offset'];
	}
	
	return $offset;
}

//----------------------------------------------------------------------------------------
// Convert page number in article to physical page in PDF
function map_page_number_to_pdf ($reference)
{
	$pdf_page = $reference->spage + get_pdf_offset($reference);
	return $pdf_page;
}


//----------------------------------------------------------------------------------------
// Generate a PII-like standard name for the article PDF
function article_pdf_name ($reference)
{
	$pdf_name = 'S' . $reference->issn 
		. $reference->year 
		. str_pad($reference->volume, 4, '0', STR_PAD_LEFT) 
		. str_pad($reference->spage, 5, '0', STR_PAD_LEFT) 
		. '.pdf';
	return $pdf_name;
}

//----------------------------------------------------------------------------------------
function import($reference)
{
	$force = true;
	$force = false;
		
	print_r($reference);
	
	if (isset($reference->volume)
		//&& isset($reference->issue)
		&& isset($reference->spage)
		&& isset($reference->year)
		&& isset($reference->issn)
		)
	{
		// put each volume in a folder
		
		$dir = "output";
		if (!file_exists($dir))
		{
			$oldumask = umask(0); 
			mkdir($dir, 0777);
			umask($oldumask);
		}	
		
		$dir .= "/" . $reference->volume;
		if (!file_exists($dir))
		{
			$oldumask = umask(0); 
			mkdir($dir, 0777);
			umask($oldumask);
		}					
	
	
		$pdf_filename = get_pdf_filename($reference);
		
		$article_pdf_filename = article_pdf_name($reference);
		
		$article_pdf_filename = $dir . "/" . $article_pdf_filename;
		
		if (file_exists($article_pdf_filename) && !$force)
		{
		}
		else
		{		
			$from = map_page_number_to_pdf($reference);
			
			if (isset($reference->epage))
			{
				$to = $from + ($reference->epage - $reference->spage);
			}
			else
			{
				$to = $from;
			}

			$command = 'gs -sDEVICE=pdfwrite -dNOPAUSE -dBATCH -dSAFER '
				. ' -dFirstPage=' . $from . ' -dLastPage=' . $to
				. ' -sOutputFile=\'' . $article_pdf_filename . '\' \'' .  $pdf_filename . '\'';

			echo $command . "\n";

			system($command);
		}
	}
}

//----------------------------------------------------------------------------------------

$filename = '';
if ($argc < 2)
{
	echo "Usage: extract.php <RIS file>\n";
	exit(1);
}
else
{
	$filename = $argv[1];
}

$file = @fopen($filename, "r") or die("couldn't open $filename");
fclose($file);

import_ris_file($filename, 'import');


?>