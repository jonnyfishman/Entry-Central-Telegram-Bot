<?php 
$name = 'Kickr Stock Bot'; 
$urls = ['wiggle'=>'https://www.wiggle.co.uk/wahoo-kickr-core-smart-turbo-trainer','wahoo'=>'https://uk.wahoofitness.com/devices/bike-trainers/kickr-core-indoor-smart-trainer','wahoo-reconditioned'=>'https://uk.wahoofitness.com/devices/bike-trainers/kickr-core-indoor-smart-trainer-reconditioned-euuk'];

$token = "469810626:AAEwOjqliMxTIS5YVmFjCHioXEV54ZRt3Uo";

$stock = [];
$stock_status = 'None in stock.';

// Get website info
function getPlaces($content,$loc) {
  preg_match('/<p class="preordernote">This product is out of stock<\/p>|"in_stock":false/', $content, $possibles);

 if ( count($possibles) == 0 ) {
	$stock[] = $loc;
	return $loc;
 }
 else {
	return false;
 }

// if ( strpos($content, '<p class="preordernote">This product is out of stock</p>') === false || strpos($content, '<span class="bem-sku-selector__status-stock bem-product-selector__radio out-of-stock js-stock-status-message">Out of stock. Let me know when in stock.</span>') === false  ) {
//        $stock[] = $loc;
//        return $loc;
// }
// else {
//        return false;
// }

}

foreach( $urls as $loc=>$url ) {
$page = file_get_contents($url);

 $chk = getPlaces($page,$loc);
 if ( $chk ) {
  $stock[] = $chk;
 }

}

 if ( count($stock) > 0 ) {
  $stock_status = 'There is possibly stock at';
   foreach ( $stock as $name ) {
    $stock_status .= ' <a href="' . $urls[$name] . '">' . $name . '</a>';
   }
  $stock_status .= '.';
 }

$data = [
  'text' => 'Tested ' . count($urls) . ' sites. ' . $stock_status,
  'stock' => count($stock),
  'chat_id' => '-1001367167212',
  'parse_mode' =>  'HTML'
];

// load data from file
$json = file_exists('kickr.json')?json_decode(file_get_contents('kickr.json')):json_decode('{"stock":"-1"}');

	if ( count($stock) != $json->{'stock'} ) {
		file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($data) );
		file_put_contents('kickr.json', '{"stock":"'.$data['stock'].'"}');
	}

?>
