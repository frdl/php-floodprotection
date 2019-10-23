# php-floodprotection
Simple IP Flood Protection

Code from https://stackoverflow.com/questions/3026640/quick-and-easy-flood-protection as Class.

````PHP
use frdl\security\floodprotection\FloodProtection;

 $FloodProtection = new FloodProtection('login', 10, 30);	
 if($FloodProtection->check($_SERVER['REMOTE_ADDR'])){
    header("HTTP/1.1 429 Too Many Requests");
    exit("Hit some *");
 }
````
