<?php
include 'common.php';

$lvStocksTblExists = false;

$indices = [

	// nifty 500 (nifty 50 + nifty next 50 + nifty midcap 150 + nifty smallcap 250)
	
	'nifty-500' => [
		'url' => 'https://www.niftyindices.com/IndexConstituent/ind_nifty500list.csv',
		'weightUrl' => 'https://blob.niftyindices.com/jsonfiles/SectorialIndex/SectorialIndexDataNIFTY%20500.js'
	],

	// base indices
	
	'nifty-50' => [
		'url' => 'https://www.niftyindices.com/IndexConstituent/ind_nifty50list.csv',
		'weightUrl' => 'https://blob.niftyindices.com/jsonfiles/SectorialIndex/SectorialIndexDataNIFTY%2050.js'
	],
	
	'nifty-next-50' => [
		'url' => 'https://www.niftyindices.com/IndexConstituent/ind_niftynext50list.csv',
		'weightUrl' => 'https://blob.niftyindices.com/jsonfiles/SectorialIndex/SectorialIndexDataNIFTY%20NEXT%2050.js'
	],
	
	'nifty-midcap-150' => [
		'url' => 'https://www.niftyindices.com/IndexConstituent/ind_niftymidcap150list.csv',
		'weightUrl' => 'https://blob.niftyindices.com/jsonfiles/SectorialIndex/SectorialIndexDataNIFTY%20MIDCAP%20150.js'
	],
	
	'nifty-smallcap-250' => [
		'url' => 'https://www.niftyindices.com/IndexConstituent/ind_niftysmallcap250list.csv',
		'weightUrl' => 'https://blob.niftyindices.com/jsonfiles/SectorialIndex/SectorialIndexDataNIFTY%20SMALLCAP%20250.js'
	],
	
	// low volatility indices
	
		// nifty 100 (nifty 50 + nifty next 50)
		'nifty-100-low-volatility-30' => [
			'url' => 'https://www.niftyindices.com/IndexConstituent/ind_Nifty100LowVolatility30list.csv',
			'weightUrl' => 'https://blob.niftyindices.com/jsonfiles/SectorialIndex/SectorialIndexDataNIFTY100%20LOW%20VOLATILITY%2030.js'
		],
		
		// nifty 500
		'nifty-low-volatility-50' => [
			'url' => 'https://www.niftyindices.com/IndexConstituent/nifty_low_Volatility50_Index.csv',
			'weightUrl' => 'https://blob.niftyindices.com/jsonfiles/SectorialIndex/SectorialIndexDataNIFTY%20LOW%20VOLATILITY%2050.js'
		],

	// quality indices
	
		// nifty 100 (nifty 50 + nifty next 50)
		'nifty100-quality-30' => [
			'url' => 'https://www.niftyindices.com/IndexConstituent/ind_nifty100Quality30list.csv',
			'weightUrl' => 'https://blob.niftyindices.com/jsonfiles/SectorialIndex/SectorialIndexDataNIFTY100%20QUALITY%2030.js'
		],	

		// nifty 200 (nifty 50 + nifty next 50 + nifty midcap 100)
		'nifty200-quality-30' => [
			'url' => 'https://www.niftyindices.com/IndexConstituent/ind_nifty200Quality30_list.csv',
			'weightUrl' => 'https://blob.niftyindices.com/jsonfiles/SectorialIndex/SectorialIndexDataNIFTY200%20QUALITY%2030.js'
		],
		
		// nifty midcap 150
		'nifty-midcap150-quality-50' => [
			'url' => 'https://www.niftyindices.com/IndexConstituent/ind_niftymidcap150quality50list.csv',
			'weightUrl' => 'https://blob.niftyindices.com/jsonfiles/SectorialIndex/SectorialIndexDataNIFTY%20MIDCAP150%20QUALITY%2050.js'
		],
	
		// nifty smallcap 250
		'nifty-smallcap250-quality-50' => [
			'url' => 'https://www.niftyindices.com/IndexConstituent/ind_niftySmallcap250_Quality50_list.csv',
			'weightUrl' => ''
		],
	
	// alpha indices

		// nifty 100 (nifty 50 + nifty next 50)
		'nifty100-alpha-30' => [
			'url' => 'https://www.niftyindices.com/IndexConstituent/ind_nifty100Alpha30list.csv',
			'weightUrl' => ''
		],
		
		// nifty 200 (nifty 50 + nifty next 50 + nifty midcap 100)
		'nifty200-alpha-30' => [
			'url' => 'https://www.niftyindices.com/IndexConstituent/ind_nifty200alpha30_list.csv',
			'weightUrl' => ''
		],
		
		// nifty 500 (nifty 50 + nifty next 50 + nifty midcap 150 + nifty smallcap 250)
		'nifty-alpha-50' => [
			'url' => 'https://www.niftyindices.com/IndexConstituent/ind_nifty_Alpha_Index.csv',
			'weightUrl' => 'https://blob.niftyindices.com/jsonfiles/SectorialIndex/SectorialIndexDataNIFTY%20ALPHA%2050.js'
		],
	
	// value indices
	
		// nifty 50
		'nifty-50-value-20' => [
			'url' => 'https://www.niftyindices.com/IndexConstituent/ind_Nifty50_Value20.csv',
			'weightUrl' => 'https://blob.niftyindices.com/jsonfiles/SectorialIndex/SectorialIndexDataNIFTY50%20VALUE%2020.js'
		],

		// nifty 500 (nifty 50 + nifty next 50 + nifty midcap 150 + nifty smallcap 250)
		'nifty500-value-50' => [
			'url' => '',
			'weightUrl' => 'https://blob.niftyindices.com/jsonfiles/SectorialIndex/SectorialIndexDataNIFTY500%20VALUE%2050.js'
		],
	
	// 500 diviend
	
	'nifty-dividend-opportunities-50' => [
		'url' => 'https://www.niftyindices.com/IndexConstituent/ind_niftydivopp50list.csv',
		'weightUrl' => 'https://blob.niftyindices.com/jsonfiles/SectorialIndex/SectorialIndexDataNIFTY%20DIVIDEND%20OPPORTUNITIES%2050.js'
	],

];

$tbl = 'indices_stocks';

$companies = [];
$industries = [];	

foreach($indices as $index => $urls) {
	
	echo "\n\n$index\n";

	if($index == 'nifty-500') {
		$stocks = getStocksCsv($urls['url'], $urls['weightUrl']);
		foreach($stocks as $stock) {
			$companies[$stock['symbol']] = $stock['company'];
			$industries[$stock['symbol']] = $stock['industry']; 	
		}
	} else {
		if(!empty($urls['weightUrl'])) {
			$stocks = getStocksJson($urls['weightUrl'], $companies, $industries);	
		} else if(!empty($urls['url'])) {
			$stocks = getStocksCsv($urls['url']);
		} else {
			continue;
		}
	}
	
	foreach($stocks as &$data) {
		$data['index'] = $index;	
		if(!$lvStocksTblExists && !isTableExists($tbl)) {
			$lvStocksTblExists = true;
			createTable($tbl, $data);
		}	
		insertTable($tbl, $data);
	}
}
