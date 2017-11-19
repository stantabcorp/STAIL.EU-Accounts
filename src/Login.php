<?php 

    namespace STAILEUAccounts;

    class Login{

        private $CSA;
        private $tfa;
        private $tfa_token;
        private $d;

        public function __construct($CSA = null, $tfa = false, $tfa_token = null){
            $this->CSA       = $CSA;
            $this->tfa       = $tfa;
            $this->tfa_token = $tfa_token;
        }

        public function isUsingTfa(){
            return $this->tfa;
        }

        public function getCSAToken(){
            return $this->CSA;
        }

        public function getTFAToken(){
            return $this->tfa_token;
        }

    }