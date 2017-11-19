<?php 

    namespace STAILEUAccounts;

    /**
	* STAIL.EU Accounts
	*
	* The new way to have accounts
	*
	* @author Thibault JUNIN <spamfree@thibaultjunin.fr>
	* @copyright 2015-2017 STAN-TAb Corp.
	* @license proprietary
	* @link https://stail.eu
	* @version 3.0.0
	*/
    class Cache{

        /**
		* @var string $cache The cache path folder
		*/
		private $cache = "./.stail_cache";

        public function __construct($dir = "./.stail_cache"){
			$this->cache = $dir;
			if(!file_exists($dir)){
				mkdir($dir);
			}
        }

        /**
		* setCache()
		*
		* This function set the cache
		*
		* @param string  $type 	 The type of data to save
		* @param string	 $name 	 The name of the file
		* @param string  $data 	 The data to save
		*
		* @return void
		*/
		public function setCache($type, $name, $data){
			if(!file_exists($this->cache."/".$type)){
				mkdir($this->cache."/".$type);
			}
			if(file_exists($this->cache."/".$type."/".$name)){
				$last = date("U", filemtime($this->cache."/".$type."/".$name));
				if(intval($last)+300 <= time()){
					unlink($this->cache."/".$type."/".$name);
					file_put_contents($this->cache."/".$type."/".$name, $data);
				}
			}else{
				file_put_contents($this->cache."/".$type."/".$name, $data);
			}
		}

		/**
		* getCache()
		*
		* This function get the cache
		*
		* @param string  $type 	 The type of data to save
		* @param string	 $name 	 The name of the file
		*
		* @return string The saved data
		*/
		public function getCache($type, $name){
			return file_get_contents($this->cache."/".$type."/".$name);
		}

		/**
		* isCached()
		*
		* This function check if a data is cached
		*
		* @param string  $type 	 The type of data to save
		* @param string	 $name 	 The name of the file
		*
		* @return boolean If the data is cached or not (also if the cache has expired)
		*/
		public function isCached($type, $name){
			if(file_exists($this->cache."/".$type."/".$name)){
				$last = date("U", filemtime($this->cache."/".$type."/".$name));
				if(intval($last)+300 <= time()){
					return false;
				}else{
					return true;
				}
			}else{
				return false;
			}
		}

    }