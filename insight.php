<style>
th {
	padding: 8px;
	text-align: center;
}

td {
	padding: 8px;
	font-weight: normal;
	white-space: nowrap;
}

tr th {
	background-color: #F4ECF7;
	color: #6C3483;
}

html, div, th, td, pre {
	font-family: Verdana;
	font-size: 13px;
}
</style>

<?php
include 'common.php';

echo "<center><h1>".Date('M Y')."</h1></center>";
	
// Nifty 50
	
	// get low volatile quality stocks
	$quality_stocks = getSymbols(['nifty-50', 'nifty-100-low-volatility-30', 'nifty100-quality-30']);
	$stocks = getStockDetails('nifty-50', $quality_stocks);
	
	// check if it's also providing alpha and undervalued
	$alpha_stocks = getSymbols(['nifty-50', 'nifty-100-low-volatility-30', 'nifty100-quality-30', 'nifty100-alpha-30']);
	$value_stocks = getSymbols(['nifty-50', 'nifty-100-low-volatility-30', 'nifty100-quality-30', 'nifty-50-value-20']);
	$dividend_stocks = getSymbols(['nifty-50', 'nifty-100-low-volatility-30', 'nifty100-quality-30', 'nifty-dividend-opportunities-50']);
	$data = getAlphaValueStocks($stocks, $alpha_stocks, $value_stocks, $dividend_stocks);
	
	echo tblStockDetails("NIFTY 50", "Low Volatile Quaility Stocks", $data);
	
	
// Nifty Next 50
	
	// get low volatile quality stocks
	$quality_stocks = getSymbols(['nifty-next-50', 'nifty-100-low-volatility-30', 'nifty100-quality-30']);
	$stocks = getStockDetails('nifty-next-50', $quality_stocks);
	
	// check if it's also providing alpha and undervalued
	$alpha_stocks = getSymbols(['nifty-next-50', 'nifty-100-low-volatility-30', 'nifty100-quality-30', 'nifty100-alpha-30']);
	$value_stocks = getSymbols(['nifty-next-50', 'nifty-100-low-volatility-30', 'nifty100-quality-30', 'nifty500-value-50']);
	$dividend_stocks = getSymbols(['nifty-next-50', 'nifty-100-low-volatility-30', 'nifty100-quality-30', 'nifty-dividend-opportunities-50']);
	$data = getAlphaValueStocks($stocks, $alpha_stocks, $value_stocks, $dividend_stocks);
	
	echo tblStockDetails("NIFTY NEXT 50", "Low Volatile Quaility Stocks", $data);
	
// Nifty Midcap 150
	
	// get low volatile quality stocks
	$quality_stocks = getSymbols(['nifty-midcap-150', 'nifty-low-volatility-50', 'nifty-midcap150-quality-50']);
	$stocks = getStockDetails('nifty-midcap-150', $quality_stocks);
	
	// check if it's also providing alpha and undervalued
	$alpha_stocks = getSymbols(['nifty-midcap-150', 'nifty-low-volatility-50', 'nifty-midcap150-quality-50', 'nifty200-alpha-30', 'nifty-alpha-50']);
	$value_stocks = getSymbols(['nifty-midcap-150', 'nifty-low-volatility-50', 'nifty-midcap150-quality-50', 'nifty500-value-50']);
	$dividend_stocks = getSymbols(['nifty-midcap-150', 'nifty-low-volatility-50', 'nifty-midcap150-quality-50', 'nifty-dividend-opportunities-50']);
	$data = getAlphaValueStocks($stocks, $alpha_stocks, $value_stocks, $dividend_stocks);
	
	echo tblStockDetails("NIFTY MIDCAP 150", "Low Volatile Quaility Stocks", $data);
	
// Nifty Smallcap 250
	
	// get low volatile quality stocks
	$quality_stocks = getSymbols(['nifty-smallcap-250', 'nifty-smallcap250-quality-50']);
	$stocks = getStockDetails('nifty-smallcap-250', $quality_stocks);
	
	// check if it's also providing alpha and undervalued
	$alpha_stocks = getSymbols(['nifty-smallcap-250', 'nifty-smallcap250-quality-50', 'nifty-alpha-50']);
	$value_stocks = getSymbols(['nifty-smallcap-250', 'nifty-smallcap250-quality-50', 'nifty500-value-50']);
	$dividend_stocks = getSymbols(['nifty-smallcap-250', 'nifty-smallcap250-quality-50', 'nifty-dividend-opportunities-50']);
	$data = getAlphaValueStocks($stocks, $alpha_stocks, $value_stocks, $dividend_stocks);
	
	echo tblStockDetails("NIFTY SMALLCAP 250", "Quaility Stocks", $data);

?>
	

