<?php
// This is necessary if the website has hotlink prevention
ini_set('user_agent','Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

// Set your Telegram information here
include_once "config.php";

// Choose the event that you are interested in
$events = array(
    'Notley Autumn Duathlon'=> // Choose a name for the entry to appear as in your Telegram notification
    118433,              // This is the can be found under the relevant url after /event/XXXXXX
    'Notley Spring Duathlon'=>
    118436,
);

$check = new Check_Entry($Telegram_token,$Telegram_chat_id,true);

foreach ($events as $nickname => $id) {
  echo $check->getInfo($id,$nickname).PHP_EOL;
}





class Check_Entry {

  private $chat_id = '';
  private $api_url = '';

  private $url = "https://www.entrycentral.com/";

  private $save_to_file = false;

  public function __construct( $token, $chat_id, $save_to_file=false) {
		$this->api_url = 'https://api.telegram.org/bot' . $token;
		$this->chat_id = $chat_id;
    $this->save_to_file = $save_to_file;
	}

  public function getInfo($event_id,$event_nickname) {
    $content = $this->getContent($event_id);

    $spaces = $this->getSpaces($event_id);
    $status = $this->getStatus($content);
    $closing_date = $this->getClosingDate($content);

    $data = 'There are <b>' . $spaces . '</b> spaces left in the <a href="' . $this->url . 'event/' . $event_id . '">' . $event_nickname . '</a>. The event is currently <i>' . $status . '</i> and the closing date is <i>' . $closing_date . '</i>';

    $safe_filename = preg_filter('/[^A-Za-z0-9._-]/', '_', $event_nickname).'.json';

    $previous_send = file_exists($safe_filename)?json_decode(file_get_contents(        $safe_filename)):json_decode('{"spaces":"null"}');

    if ($this->save_to_file == true ) {

        file_put_contents($safe_filename,'{"spaces":"'.$spaces.'","status":"'.$status.'","closing_date":"'.$closing_date.'"}');

        if ($spaces == $previous_send->{'spaces'}) return 'No change in spaces';
    }

    return $this->send($data);

  }

  private function getClosingDate($content) {
    preg_match_all('/([0-9]{2} [a-z|A-Z]{3} [0-9]{4})/i', $content, $possibles);
    return $possibles[0][count($possibles)-1];
  }
  private function getStatus($content) {
    return strpos( $content, "<p>Status: Open</p>" ) > 0?'open':'closed';
  }

  private function getContent($event_id) {
    return file_get_contents($this->url . 'event/' . $event_id);
  }

  private function getSpaces($event_id) {
    return json_decode( file_get_contents($this->url . 'places_available/event/' . $event_id . '.json') )->{'places_available'};
  }

  public function send($message) {
		$text = trim($message);

  	if (strlen($text) == 0) return 'empty message';

  			$send = $this->api_url . "/sendmessage?parse_mode=html&chat_id=" . $this->chat_id . "&text=" . urlencode($text);
  			file_get_contents($send);

        if ( strpos( $http_response_header[0], "200" ) !== false ) {
          return 'Successfully sent';
        }
        else {
          return 'Sending failed. Returned error: '.$http_response_header[0];
        }




	}

}
