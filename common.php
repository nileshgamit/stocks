<?php
$database = "stocks";

$db = dbConnect($database);

function fetchFile($url) {
	
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'GET',
	  CURLOPT_USERAGENT=>'PostmanRuntime/7.29.0',
      CURLOPT_ENCODING=>'gzip, deflate, br',
      CURLOPT_HTTPHEADER=>array(
		'Accept: */*',
		'Accept-Language: en-US,en;q=0.5',
		'Accept-Encoding: gzip, deflate, br',
		'Connection: keep-alive',
		'Upgrade-Insecure-Requests: 1',
      ),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	return $response;
}

function dbConnect($database) {
	
	$host = "localhost";
	$login = "root";
	$password = "";
	$port = 3306;
	
	return mysqli_Connect($host, $login, $password, $database, $port);
}

function isTableExists($tbl) {
	
	global $db;
	
	$sql = "select 1 from `$tbl` LIMIT 1";
	$result = mysqli_query($db, $sql);

	if($result !== false) {
	   return true;
	}
	
	return false;
}

function createTable($tbl, $data) {
	
	global $db;
	
	$sql = "CREATE TABLE `$tbl` (";
		
	$fields = [];
	$fields[] = "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY";
	foreach($data as $key => $val) {
		$fields[] = "`$key` varchar(255) DEFAULT NULL";
	}
	$sql .= join(',', $fields);
	
	$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1";
	
	@mysqli_query($db, $sql);
}

function insertTable($tbl, $data) {
	
	global $db;
	
	$sql = "INSERT INTO $tbl SET ";
	
	$dataFields = [];
	foreach($data as $key => $val) {
		$dataFields[] = "`$key` = \"$val\"";
	}
	$sql .= join(',', $dataFields);
	
	@mysqli_query($db, $sql);
}

function isRecordExists($tbl, $field, $val) {
	
	global $db;
	
	$sql = "SELECT * FROM `$tbl` WHERE `$field` = '$val' LIMIT 1";
	
	$result = @mysqli_query($db, $sql);
	if (!$result) {
		return 0;
	}
	
	$data = mysqli_fetch_object($result);
	if (!$data) {
		return 0;
	}
	
	return $data->{$field};
}

function getStocksCsv($url, $weightUrl = false) {
	
	$weights = [];
	if($weightUrl) {
		$json = fetchStocksJson($weightUrl);
		$weights = getWeight($json->Data[0]->groups);
	}
	
	$content = fetchFile($url);	
	$stocks = explode("\n", $content);
	
	$data = [];
	foreach($stocks as $i => $stock) {
		if(!$i || empty($stock)) {
			continue;
		}
		
		list($company, $industry, $symbol, , ) = explode(",", $stock);
		
		$weight = (isset($weights[$symbol])) ? $weights[$symbol] : 0;
		
		$data[] = [
			'company' => $company,
			'industry' => $industry,
			'symbol' => $symbol,
			'weight' => $weight
		];
	}
	
	return $data;
}

function fetchStocksJson($url) {
	$content = fetchFile($url);
	$content = str_replace(");", "", $content);
	$content = str_replace("modelDataAvailable(", "", $content);
	$content = str_replace(",],", "],", $content);
	$content = str_replace(",]}", "]}", $content);
	$content = str_replace("label:", "\"label\":", $content);
	$content = str_replace("file:", "\"file\":", $content);
	$content = "{\"Data\": [" . $content . "]}";
	return json_decode($content);
}

function getWeight($groups) {
	$weights = [];
	foreach($groups as $obj) {
		foreach($obj->groups as $stock) {
			list($symbol, ) = explode(" ", $stock->label);
			$weights[$symbol] = $stock->weight;
		}
	}
	return $weights;
}

function getStocksJson($url, $companies, $industries) {
	$json = fetchStocksJson($url);
	$weights = getWeight($json->Data[0]->groups);	
	$data = [];
	foreach($weights as $symbol => $weight) {
		$company = (isset($companies[$symbol])) ? $companies[$symbol] : '';
		$industry = (isset($industries[$symbol])) ? $industries[$symbol] : '';
		
		$data[] = [
			'company' => $company,
			'industry' => $industry,
			'symbol' => $symbol,
			'weight' => $weight
		];
	}
	
	return $data;
}

function getSymbols($indices) {
	
	global $db;
	
	$sql = "SELECT `symbol`
		FROM indices_stocks 
		WHERE ";
		
	$where = [];
	foreach($indices as $index) {
		$where[] = "`index`='$index'";
	}
	
	$sql .= join(' OR ', $where);
	
	$sql .=	" GROUP BY `symbol` HAVING COUNT(*) = ". count($indices);
	
	$result = @mysqli_query($db, $sql);
	if (!$result) {
		return 0;
	}
	
	$data = [];
	while ($obj = $result->fetch_assoc()) {
		$data[] = $obj['symbol'];
	}

	if (!$data) {
		return [];
	}
	
	return $data;
}

function getStockDetails($index, $stocks) {
	
	global $db;
	
	$updatedStocks = [];
	foreach($stocks as $stock) {
		$updatedStocks[] = "'".$stock."'";
	}
	
	$sql = "SELECT *
		FROM indices_stocks 
		WHERE `index` = '$index' AND `symbol` IN (".join(',', $updatedStocks).") 
		ORDER BY industry ASC, weight DESC";
	
	$result = @mysqli_query($db, $sql);
	if (!$result) {
		return 0;
	}
	
	$data = [];
	while ($obj = $result->fetch_assoc()) {
		$data[] = $obj;
	}

	if (!$data) {
		return [];
	}
	
	return $data;
}

function getAlphaValueStocks($stocks, $alpha_stocks, $value_stocks, $dividend_stocks, $quality = true) {

	if(empty($stocks)) {
		return;
	}
	
	$data = [];
	foreach($stocks as &$stock) {
			
		$alpha = '';
		if(in_array($stock['symbol'], $alpha_stocks)) {
			$alpha = 'alpha';
		}
		
		$value = '';
		if(in_array($stock['symbol'], $value_stocks)) {
			$value = 'under valued';
		}
		
		$dividend = '';
		if(in_array($stock['symbol'], $dividend_stocks)) {
			$dividend = 'provides dividend';
		}
		
		if(!$quality) {
			if($alpha == '' && $value == '' && $dividend == '') {
				continue;
			}
		}
		
		$stock['alpha'] = $alpha;
		$stock['value'] = $value;
		$stock['dividend'] = $dividend;
		
		$data[] = $stock;
	}

	return $data;
}

function tblStockDetails($index, $heading, $stocks) {
	
	if(empty($stocks)) {
		return;
	}
	
	$component = "<center><h3>$index</h3> <span style='color:#3498DB'>".count($stocks)." ".$heading."</span></center>";
	$component .= "<div style='vertical-align: top; padding-top: 10px; padding-bottom: 10px;'>
		<table width=100% border=1 style='border-collapse: collapse; border-spacing: 30px;'>";

	$component .= "<tr>
		<th style='vertical-align: top'>Company</th>
		<th style='vertical-align: top'>Symbol</th>
		<th style='vertical-align: top'>Alpha</th>
		<th style='vertical-align: top'>Value</th>
		<th style='vertical-align: top'>Dividend</th>
		<th style='vertical-align: top'>Weight(%)</th>
	</tr>";
	
	$industry = '';
	$total_weight = 0;
	
	foreach($stocks as $stock) {
		
		if($industry != $stock['industry']) {
			$industry = $stock['industry'];
			$component .= "<tr>
				<td colspan='6' style='text-align: center'><b>".$stock['industry']."</b></td>
			</tr>";
		}
		
		$total_weight = $total_weight + $stock['weight'];
		
		$component .= "<tr>
			<td style='vertical-align: top'>".$stock['company']."</td>
			<td style='vertical-align: top'>".$stock['symbol']."</td>
			<td style='vertical-align: top; text-align: center;'>".$stock['alpha']."</td>
			<td style='vertical-align: top; text-align: center;'>".$stock['value']."</td>
			<td style='vertical-align: top; text-align: center;' text-align: center;>".$stock['dividend']."</td>
			<td style='vertical-align: top; text-align: right;'>".$stock['weight']."</td>
		</tr>";	
	}
	
	$component .= "<tr>
		<td colspan='6' style='text-align: center'>Total Weight in $index: <b>$total_weight%</b></td>
	</tr>";
	
	$component .= "</table></div>";
	
	return $component;
}
