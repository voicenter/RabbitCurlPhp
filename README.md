# RabbitCurlPhp
RabbitMQ  Curl PHP class for publishing  event to one of the server in the list 

    echo "Lets start Testing Curl Rabbit ....";  
    //1.Load the class  
    require_once("RabbitCurlPhp.php");  
    $rabbit = new \RabbitCurlPhp\RabbitCurlPhp();  
    //2.Make a stdClass payload to send  
    $payLoad = new stdClass();  
    $payLoad->foo="bar";  
    //3.Load the Request data  
    $rabbit->setPublishRequest("amq.headers",array(["xx"=>"1","yy"=>"2"]),$payLoad);  
    //4.Fire the request  
    var_dump($rabbit->Post());
