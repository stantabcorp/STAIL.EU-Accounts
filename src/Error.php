<?php 

    namespace STAILEUAccounts;

    class Error{

        private $code;
        private $message;

        public function __construct($code, $msg){
            $this->code = $code;
            $this->message = $msg;
        }

        public function getMessage(){
            return $this->message;
        }

        public function getCode(){
            return $this->code;
        }

    }