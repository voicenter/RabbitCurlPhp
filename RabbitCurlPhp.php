<?php
namespace RabbitCurlPhp;

use mysql_xdevapi\Exception;
class RabbitCurlPhp
{
    private $Servers;
    private $PublishRequest;
    private $serverIndex = 0;

    function __construct() {
              $this->Servers =$this->GetServersList();
            var_dump($this->Servers[0]);
    }

    /**
     * @return array - List of server to try send the request to ...
     */
    function GetServersList() {
      // $ServersList = new stdClass;
        return [
            ['Host' => 'http://127.0.0.2:15672', 'User' => 'guest1', 'Password' => 'guest' ],
            ['Host' => 'http://127.0.0.1:15672', 'User' => 'guest', 'Password' => 'guest' ]
        ];
    }


    /**
     * @param $exchange - exchange name should be from type headers
     * @param $headers - Object that define the headers
     * @param $payload - object to be send as json payload
     */
    public function setPublishRequest($exchange,$headers, $payload){
        $pr = array (
            'vhost' => '/',
            'name' => $exchange,
            'properties' =>
                array (
                    'delivery_mode' => 2,
                    'headers' =>(object)$headers[0],
                ),
            'routing_key' => '',
            'delivery_mode' => '2',
            'payload' => json_encode((object)$payload),
            'headers' => (object)$headers[0],
            'props' =>
                array (
                ),
            'payload_encoding' => 'string',
        );
        $this->PublishRequest=$pr;
        var_dump($pr);
    }
    public function Post(){
        while($this->serverIndex<=count( $this->Servers)){
            echo "\n \n Lets try server  ". $this->serverIndex . "\n\n";
            $routed = $this->PostToServer();
            if($routed===true){
                echo "All good ! \n ";
                return true ;
            }

            $this->serverIndex++;
        }
    }
    public function PostToServer(){

        try{
            $url = $this->Servers[$this->serverIndex]["Host"].  "/api/exchanges/%2F/amq.headers/publish";
            $prBody = json_encode((object)$this->PublishRequest);
            echo("Sending to :".$url . " data: ".$prBody)    ;

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>$prBody,
                //     "{\"vhost\":\"/\",\"name\":\"amq.headers\",\"properties\":{\"delivery_mode\":2,\n\"headers\":{\"xx\":\"1\",\"yy\":\"2\",\"zz\":\"3\"}},\"routing_key\":\"\",\"delivery_mode\":\"2\",\"payload\":\"{\\\"foo\\\":\\\"bar\\\"}\",\"headers\":{\"xx\":\"1\",\"yy\":\"2\",\"zz\":\"3\"},\"props\":{},\"payload_encoding\":\"string\"}",
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Basic '. base64_encode($this->Servers[$this->serverIndex]["User"].":".$this->Servers[$this->serverIndex]["Password"]),
                    "cache-control: no-cache",
                    "content-type: application/json"
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "\n cURL Error #:" . $err;
                return false;
            } else {
                echo "\n!!!!!!!!!!!!!!". $response ."!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!";
                if(count_chars($response)>0){
                    $responseObject= json_decode($response);
                   if(isset($responseObject->routed)&&$responseObject->routed===true){
                       return true;
                   }else{
                       return false;
                   }

                }else{
                    return false;
                }
                echo " \n cURL response #:" .$response;
            }
        }catch (Exception $e){
            return false;
        }

    }

}
