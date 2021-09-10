<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Tracy\Debugger;
use PHPHtmlParser\Dom;
use Nette\Utils\Strings;

Debugger::enable();

// Proměnné pro nalezení správných prvků, které se mají parsovat
$address = "https://obec.brnenec.cz";
$params = "/Uredni-deska/";
$outputDirectory = "output/reports/";

if (!file_exists($outputDirectory)) {
	mkdir($outputDirectory, 0777, true);
}

$innerCssSelector = "#ur_deska_detail a.file";
$innerCssSelector2 = "#ur_deska_detail a.highslide";

$client = new Client();
$dom = new Dom;
$dom2 = new Dom;
$dom3 = new Dom;

$home = $client->get($address . $params);
$dom->loadStr($home->getBody());
$domBox1 = $dom->find("#content .divContent .article");

function implodeArrayofArrays($array)
{
	$output = '';
	foreach ($array as $subarray) {
		$output .= implode(";", $subarray);
	}
	return $output;
}
foreach ($domBox1 as $a) {
	$i = 0;
	$data = [];

	$category = $a->find('h2')->innerText();
	$href = $a->find('a');

	if ($category == 'Archív') {
		continue;
	} elseif ($category == 'Usnesení') {
		continue;
	}
	$url = $href->href;

	$home2 = $client->get($address . $url);
	$dom2->loadStr($home2->getBody());
	$domBox2 = $dom2->find("#content .divContent .newsSummary div");

	foreach ($domBox2 as $a2) {
		$title = $a2->find('strong')->innerText();
		$href2 = $a2->find('a');
		$url2 = $href2->href;

		$home3 = $client->get($address . $url2);
		$dom3->loadStr($home3->getBody());
		$domBox3 = $dom3->find('#content .divContent .newsSummary .newsItemDetail');
		$attachments = [];
		foreach ($domBox3 as $a3) {


			$attachment = $a3->find('.newsBody a');

			foreach ($attachment as $att) {

				$attachments[] = $att->getAttribute('href');
				$date = $a3->find('.date')->innerText();
			}
			// dump($attachment);
			$data[] = [
				'cat' => $category,

				'datum' => $date,

				'název' => $title,

				'přílohy' => implode(';', $attachments) . '<br>',

			];
		}
	}
	// dump(implodeArrayofArrays($data));
	echo implodeArrayofArrays($data);






	//




}


 






// $innerLine;

// $header = [
// "typ" => "Typ",
// "name" => "Titulek",
// "text" => "Text",
// "date" => "Datum vytvoření",
// "publishUp" => "Datum zobrazení",
// "publishDown" => "Datum stažení",
// "attachments" => "Přílohy"
// ];

// foreach ($header as $key => $value) {
// $innerLine .= $value . ";";
// }
// $innerLine .= "\n";

// $categories = $domBox->find("h3.nazev");
// foreach ($categories as $index => $categoryList) {
// $category[$index] = Strings::trim($categoryList->innerText());
// }

// $categoryAsType = array(
// "Dokumenty" => 50,
// "Vyhlášky" => 51,
// "Rozpočet" => 52,
// "Závěrečný účet" => 53,
// );

// $innerData = $domBox->find("div.ur_deska_kat table");
// foreach ($innerData as $index => $inner) {

// $rows = $inner->find("tr");
// foreach ($rows as $key => $row) {

// if ($key !== 0) {

// 	$rowName = $row->find("td")[0]->innerText();
// 	$rowDate = $row->find("td")[2]->innerText();

// 	$contentLink = $row->find("td a")->getAttribute('href');

// 	$innerClient = new Client();
// 	$innerHome = $innerClient->get($address . $contentLink);

// 	$innerDom = new Dom;
// 	$innerDom->loadStr($innerHome->getBody());

// 	$innerContent = $innerDom->find($innerCssSelector);
// 	$innerContent2 = $innerDom->find($innerCssSelector2);

// 	if (count($innerContent)) {

// 		$fileLink = html_entity_decode($address . "/" . $innerContent->getAttribute('href'));

// 		$fileExtension = pathinfo($innerContent->getAttribute('href'))['extension'];

// 		$fileName = $innerContent->getAttribute('title');
// 		$fileName = explode(": ", $fileName);
// 		$fileName = explode(", ", $fileName[1]);
// 		$fileName = explode(", ", $fileName[0]);
// 		$fileName = Strings::webalize($fileName[0]);

// 		file_put_contents($outputDirectory . $fileName . "." . $fileExtension, file_get_contents($fileLink));

// 		$fileToDownload = $fileName . "." . $fileExtension;
// 	} elseif (count($innerContent2)) {

// 		$fileLink = html_entity_decode($address . "/" . $innerContent2->getAttribute('href'));

// 		$fileExtension = pathinfo($innerContent2->getAttribute('href'))['extension'];
// 		$fileExtension = explode("?", $fileExtension);
// 		$fileExtension = $fileExtension[0];

// 		$fileName = $innerContent2->find("img")->getAttribute('title');
// 		$fileName = Strings::webalize($fileName . "-" . $key);

// 		file_put_contents($outputDirectory . $fileName . "." . $fileExtension, file_get_contents($fileLink));
// 		$fileToDownload = $fileName . "." . $fileExtension;
// 	} else {
// 		$fileToDownload = "";
// 	}

// 	$innerLine .= $categoryAsType[$category[$index]] . ";";
// 	$innerLine .= Strings::trim($row->find("td")[0]->innerText()) . ";";
// 	$innerLine .= Strings::trim($row->find("td")[0]->innerText()) . ";";
// 	$innerLine .= Strings::trim($row->find("td")[2]->innerText()) . ";";
// 	$innerLine .= Strings::trim($row->find("td")[2]->innerText()) . ";";
// 	$innerLine .= Strings::trim($row->find("td")[3]->innerText()) . ";";
// 	$innerLine .= $fileToDownload . ";";
// 	$innerLine .= "\n";
// }
// }
// }

// echo $innerLine;
// $outputCsv = $outputDirectory . "reports.csv";
// file_put_contents($outputCsv, $innerLine);
