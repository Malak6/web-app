<?php
namespace App\Http\Message;
class Message {
    //Create the msg property
    private $msg = [];

    public function __construct(){
        $this->msg =
            [ "key1" => 5 , "key2" => "value1" ,"key3" => "value1" ];
    }
    //Get the msg property 
    public function getMessage(){
        return $this->msg ;
    }
    // update to handle array of keys
    public function setMessage($key,$value){
        if(!$this->msg[$key])  return "no such property";
        $this->msg[$key] = $value;
        return $this->msg;
    }
}
