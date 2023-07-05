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

function getAlphaValueStocks($stocks, $lv_stocks, $alpha_stocks, $value_stocks, $dividend_stocks, $quality = true) {

	if(empty($stocks)) {
		return;
	}
	
	$data = [];
	foreach($stocks as &$stock) {
		
		$lv = '';
		if(in_array($stock['symbol'], $lv_stocks)) {
			$lv = 'low volatile';
		}
			
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
		
		$stock['lv'] = $lv;
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
	
	$component = "<center><h1>$index</h1> <span style='color:#2E86C1;'>".count($stocks)." ".$heading."</span></center>";
	$component .= "<div style='vertical-align: top; padding-top: 10px; padding-bottom: 10px;'>
		<table width=100% border=1 style='border-collapse: collapse; border-spacing: 30px;'>";

	$component .= "<tr>
		<th style='vertical-align: top'>Company</th>
		<th style='vertical-align: top'>Symbol</th>
		<th style='vertical-align: top'>Volatility</th>
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
				<td colspan='7' style='text-align: center'><b>".$stock['industry']."</b></td>
			</tr>";
		}
		
		$weight = $stock['weight'];
		
		$total_weight = $total_weight + $weight;
		
		$bgColor = ''; $color = '';
		if($weight >= 5) {
			$bgColor = '#2874A6'; $color = '#FFF';
		} else if($weight >= 3) {
			$bgColor = '#3498DB'; $color = '#FFF';
		} else if($weight >= 1) {
			$bgColor = '#AED6F1'; $color = '';
		} else if($weight > 0) {
			$bgColor = '#EBF5FB'; $color = '';
		}
				
		$component .= "<tr>
			<td style='vertical-align: top'>".$stock['company']."</td>
			<td style='vertical-align: top'>".$stock['symbol']."</td>
			<td style='vertical-align: top; text-align: center; color: #138D75;'>".$stock['lv']."</td>
			<td style='vertical-align: top; text-align: center; color: #3498DB;'>".$stock['alpha']."</td>
			<td style='vertical-align: top; text-align: center; color: #E74C3C;'>".$stock['value']."</td>
			<td style='vertical-align: top; text-align: center; color: #9B59B6;'>".$stock['dividend']."</td>
			<td style='vertical-align: top; text-align: right; background-color: $bgColor; color: $color;'>".$stock['weight']."</td>
		</tr>";	
	}
	
	$component .= "<tr>
		<td colspan='7' style='text-align: center'>Total Weight in $index: <b>$total_weight%</b></td>
	</tr>";
	
	$component .= "</table></div>";
	
	return $component;
}

function getUniqueIndustries() {
	
	global $db;
	
	$sql = "SELECT DISTINCT `industry` FROM `indices_stocks` ORDER BY industry";
	
	$result = @mysqli_query($db, $sql);
	if (!$result) {
		return 0;
	}
	
	$data = [];
	while ($obj = $result->fetch_assoc()) {
		if(!empty($obj['industry'])) {
			$data[] = $obj['industry'];
		}
	}

	if (!$data) {
		return [];
	}
	
	return $data;	
}

function getIndustryWeightForIndex($industry, $indices) {
	
	global $db;
	
	$data = [];
	
	foreach($indices as $index) {
		
		$data[$index] = '0.00';
		
		$sql = "SELECT `industry`, ROUND(SUM(`weight`), 2) as total_weight FROM `indices_stocks` WHERE `index` = '$index' and `industry` = '$industry' GROUP BY industry ORDER BY total_weight DESC";
	
		$result = @mysqli_query($db, $sql);
		if (!$result) {
			continue;
		}
			
		while ($obj = $result->fetch_assoc()) {
			$data[$index] = $obj['total_weight'];
		}
	}
	
	if (!$data) {
		return [];
	}
	
	return $data;	
}

function tblIndustryDetails($industries, $indices) {
	
	if(empty($industries)) {
		return;
	}
	
	$component = "<center><h1>Industry Index Weight (%)</h1></center>";
	$component .= "<div style='vertical-align: top; padding-top: 10px; padding-bottom: 10px;'>
		<table width=100% border=1 style='border-collapse: collapse; border-spacing: 30px;'>";

	$component .= "<tr>
		<th>Industry</th>";	
		foreach($indices as $index) {
			$component .= "<th>$index</th>";
		}		
		$component .= "</tr>";
		
	foreach($industries as $industry => $data) {
		$component .= "<tr>
			<td style='vertical-align: top'>$industry</td>";
			foreach($data as $index => $weight) {
				
				$bgColor = ''; $color = '';
				if($weight >= 30) {
					$bgColor = '#2874A6'; $color = '#FFF';
				} else if($weight >= 20) {
					$bgColor = '#3498DB'; $color = '#FFF';
				} else if($weight >= 15) {
					$bgColor = '#85C1E9'; $color = '';
				} else if($weight >= 10) {
					$bgColor = '#AED6F1'; $color = '';
				} else if($weight >= 5) {
					$bgColor = '#D6EAF8'; $color = '';
				} else if($weight > 0) {
					$bgColor = '#EBF5FB'; $color = '';
				}
				
				$component .= "<td style='text-align: right; background-color: $bgColor; color: $color;'>$weight</th>";
			}
		$component .= "</tr>";
	}
	
	$component .= "</table></div>";
	
	return $component;
}


