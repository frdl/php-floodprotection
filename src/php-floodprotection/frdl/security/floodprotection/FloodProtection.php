<?php
namespace frdl\security\floodprotection;
/**
* https://stackoverflow.com/questions/3026640/quick-and-easy-flood-protection
*/
/*
use frdl\security\floodprotection\FloodProtection;
 $FloodProtection = new FloodProtection('login', 10, 30);	
 if($FloodProtection->check($_SERVER['REMOTE_ADDR'])){
    header("HTTP/1.1 429 Too Many Requests");
    exit("Hit some *");
 }
*/
class FloodProtection
{
 protected $dir;
 protected $limit;
 protected $duration;	
 protected $autoclean = true;	
 protected $name;
 protected $pfx;	
	
 public function __construct($name = '', $limit = 10, $duration = 30, $dir = null, $autoclean = true){
	 if(null === $dir){
		 $dir = $this->getCacheDir('frdl-floodprotection');
	 }
	 $this->dir = $dir;
	 $this->name = $name;
	 $this->pfx = 'fp_'  . strlen($this->name) .'_'. sha1($this->name).'_';
	 $this->autoclean = $autoclean;
	 $this->limit = $limit;
	 $this->duration = $duration;
 }
	
// Record and check flood.
// Return true for hit.
 public function check($id = null){
	if(null === $id){
	   $id = $_SERVER['REMOTE_ADDR'];	
	}
	 
	if(!is_dir($this->dir)){
	  mkdir($this->dir, 0755, true);	
	}
	 
    $fp = fopen(rtrim($this->dir, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . $this->pfx . basename($id), 'a+');
    fwrite($fp, pack('L', time()));
    if(fseek($fp, -4 * $this->limit, \SEEK_END) === -1) {
        return false;
    }
    $a = unpack('L', fread($fp, 4));	 
    $time = reset($a);
    fclose($fp);
    if(time() - intval($time) < $this->duration) {
        if($this->autoclean){
            $this->prune();
        }
        return true;
    }
    return false;
 }
// Clean the pool.
 public function prune(){
    $handle = opendir($this->dir);
    while(false!==($entry=readdir($handle))){
        $filename = rtrim($this->dir, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . $entry;
        if(time() - filectime($filename) > $this->duration && substr($entry, 0, strlen($this->pfx)) === $this->pfx){
            unlink($filename);
        }
    }
    closedir($handle);
  }
	
	
 public function getCacheDir($name = 'frdl-floodprotection'){
	    $name = strtoupper($name);
	 
		  $_ENV['FRDL_HPS_CACHE_DIR'] = ((isset($_ENV['FRDL_HPS_CACHE_DIR'])) ? $_ENV['FRDL_HPS_CACHE_DIR'] 
                   : sys_get_temp_dir() . \DIRECTORY_SEPARATOR . get_current_user(). \DIRECTORY_SEPARATOR . 'cache-frdl' . \DIRECTORY_SEPARATOR
					  );
	  
	  
          $_ENV['FRDL_HPS_PSR4_CACHE_DIR'] = ((isset($_ENV['FRDL_HPS_PSR4_CACHE_DIR'])) ? $_ENV['FRDL_HPS_PSR4_CACHE_DIR'] 
                   : $_ENV['FRDL_HPS_CACHE_DIR']. 'psr4'. \DIRECTORY_SEPARATOR
					  );
 
		 
		  
 
	 
	 if(!empty($name)){		 
        $_ENV['FRDL_HPS_'.$name.'_CACHE_DIR'] = ((isset($_ENV['FRDL_HPS_'.$name.'_CACHE_DIR'])) ? $_ENV['FRDL_HPS_'.$name.'_CACHE_DIR'] 
                   : rtrim($_ENV['FRDL_HPS_CACHE_DIR'],'\\/'). \DIRECTORY_SEPARATOR.$name. \DIRECTORY_SEPARATOR
					  );			
	 }
	 
	 return (empty($name)) ? $_ENV['FRDL_HPS_CACHE_DIR'] : $_ENV['FRDL_HPS_'.$name.'_CACHE_DIR'];
   }
	
}
