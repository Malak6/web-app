<?php

namespace App\Http\Message;

class Message {
    //Create the sigle instance
    private static $instance = null;
    //Create the msg property
    private $msg = [];

    //Make the constructor private so that this class cannot be instantiated
    private function __construct(){
        $this->msg =
            [
                "key1" => 5 ,
                "key2" => "value1" ,
                "key3" => "value1" ,
            ];
    }
    //Get the only instance available
    public static function getInstance(){
        if (self::$instance == null) {
            self::$instance = new Message();
        }
        return self::$instance;
    }
    //Get the msg property 
    public function getMessage(){
        return $this->msg ;
    }


// public static function setMessage($){
//     return $msg;
// }

    
}
