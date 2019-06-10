<?php
/**
* Avito to telegram
* 
* 
* 
* @author kamaz__ <admin@tavria-club.ru>
* @version 1.3
*/

/**
* including PHP Simple Html DOM Parser
*/
 include('simple_html_dom.php');
 
/**
* SQL class
*/
 class Sql
{
	protected $dbname = 'avito';
	protected $dbuser = 'avito'     ;
	protected $dbpass = 'adsl17'    ;
	protected $dbhost = 'localhost' ; 
	public $mysqli;
	
	 public function action($query)
	{
		$this->mysqli = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname) or die(mysqli_error());
		$this->mysqli->query("SET NAMES 'utf8'");
		$result = $this->mysqli->query($query);
		$this->mysqli->close();		
	   return $result;
	}
	
	 public function ident()
	{
	   return $this->mysqli; 
	}
}

/**
* Telegram class
*/
class Telegram extends Sql
{         
    private $token    =   "471933606:AAGtCM5eMJRntEJeuvg-g67KdWLY64Z1cUw";


        private function exec_curl_request($handle) 
        {
          $response = curl_exec($handle);

          if ($response === false) 
          {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            error_log("Curl returned error $errno: $error\n");
            curl_close($handle);
            return false;
          }

          $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
          curl_close($handle);

          if ($http_code >= 500) 
          {
               // do not wat to DDOS server if something goes wrong
               sleep(10);
               return false;
          } 
          else if ($http_code != 200) 
          {
            $response = json_decode($response, true);
            error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
                if ($http_code == 401) 
                {
                      throw new Exception('Invalid access token provided');
                }
            return false;
          }
          else
          {
                $response = json_decode($response, true);
                if (isset($response['description']))
                {
                     error_log("Request was successfull: {$response['description']}\n");
                }
              $response = $response['result'];
          }

          return $response;
        }
        
        public function send($method, $parameters) 
        {
          
        
          if (!is_string($method)) {
            error_log("Method name must be a string\n");
            return false;
          }

          if (!$parameters) {
            $parameters = array();
          } else if (!is_array($parameters)) {
            error_log("Parameters must be an array\n");
            return false;
          }

          $parameters["method"] = $method;
          $handle = curl_init('https://api.telegram.org/bot'.$this->token.'/');
          curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
          curl_setopt($handle, CURLOPT_TIMEOUT, 60);
          curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
          curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        
          $res = $this->exec_curl_request($handle);
         // file_put_contents('logs/output.log',$res);
        }        
        
}

/**
* Avito class
*/
class Avito extends Telegram
{
	
/**
* $cars is array of search URL for Avito
* Example: 
* $cars = array("0" => "https://www.avito.ru/samara/avtomobili/s_probegom/toyota?pmax=1500000&pmin=0&s=101&user=1&f=188_898b0&i=1");
*/	
	private $cars = array();
	
	private function car()
	{
		foreach($this->cars as $key=>$val)
		{
			$html = file_get_html($val);
			foreach($html->find('a.item-description-title-link') as $e)
			{
				$link = $e->href;
				$count = mysqli_fetch_array($this->action("SELECT count(*) FROM `av_cars` WHERE `link` = '".$link."'"));
				if($count[0]==0)
				{
					$this->action("INSERT INTO `av_cars`(`id`, `link`, `desc`, `name`) VALUES ('','".$link."','','')");
					
					$this->mess($link);				
				}
			}			
		}
	}
	
	private function price($link)
	{
		$html = file_get_html("https://avito.ru".$link);
		foreach($html->find('span.price-value-string') as $p)
		{
			$prc = preg_replace('/[^0-9]/', '', $p->plaintext);
			$price = "Цена: ".$prc;
			
		}	
		return $price;
	}
	
	private function image($link)
	{
		$html = file_get_html("https://avito.ru".$link);
		foreach($html->find('li.gallery-extended-img-frame_state-selected') as $i)
		{
			preg_match_all('/data-alt-url="(.*?)"/i',$i->outertext,$r); 
			//$img = $img."\r\n".$i->attr['data-alt-url'];;
			print_r($r); 
		}		
	}
	
	private function mess($link)
	{
		$html = file_get_html("https://avito.ru".$link);
		$str = "";
		$img = "";
		$price = "0";
		foreach($html->find('li.item-params-list-item') as $e)
		{
			$str = $str."\r\n".$e->plaintext;
			$tst = explode("\r\n",$str);
			
		}
		//print_r($tst);
		$s = preg_replace('/[^0-9]/', '', $tst["14"]);

		
		$price = $this->price($link);
		$txt = $price."\r\n".$str;
					$keyboard = array(
'inline_keyboard' => array(array(array('text'=>'Перейти','url'=>'https://avito.ru/'.$link.'','callback_data' => '123')))
); 
	if((int)$s<=3)
	{
		
/**
* Example call send method
*/
		
		/// Mikhail
		$this->send("sendMessage", array('chat_id' => (int)"/* telegram user ID */", 'text' => $txt, 'reply_markup' => array(
        'keyboard' => array(),
        'one_time_keyboard' => true,'resize_keyboard' => true)));
		
		$this->send("sendMessage", array('chat_id' => int)"/* telegram user ID */", 'text' => 'https://avito.ru/'.$link.'', 'reply_markup' => array(
        'keyboard' => array(),
        'one_time_keyboard' => true,'resize_keyboard' => true)));
	}

		
		
	}
	
	public function i()
	{
		$this->car();
	}
}

	$avito = new Avito();
	$avito->i();	
?>