<?php
namespace frdlweb;
use frdl\Flow\EventEmitter as EventEmitter;
use frdlweb\Storage\PleskPdo as PleskPdo;

require __DIR__ . DIRECTORY_SEPARATOR .'bootstrap.php';

$myHost = 'frdl.de';
$router = new Endpoint\Router();


class PleskInternalRouter extends Endpoint\MultiRouter {
	
	public function set_db(&$DB = null ){
		if(!is_object($DB)){
			$this->db = new PleskPdo();
			$DB = &$this->db;
		}else{
			$this->db = $DB;
		}
		
		return $this;
	}
	
	public function test($params = null){
	   echo 'TEST '.__METHOD__.PHP_EOL;	
	   echo print_r($params, true).PHP_EOL;
	   return $params;	
	}
	   
}


if(8443 === intval($router->server['port'])) {
  $InternalRouter = new PleskInternalRouter();  //  extends \frdlweb\Endpoint\MultiRouter
}elseif($myHost === $router->server['name']){
  $InternalRouter = new FrdlwebInternalRouter(); //  extends \frdlweb\Endpoint\MultiRouter
}else{
  $InternalRouter = new WebfanInternalRouter(); //  extends \frdlweb\Endpoint\MultiRouter
}
$router = &$InternalRouter->Router;








$router->map('GET','/test/', function() use(&$InternalRouter){

    $InternalRouter->stateEmitter->emit('test', array('test#home'));
    $InternalRouter->stateEmitter->emit('module', 'test');
    $InternalRouter->stateEmitter->emit('ModuleTest:before', $InternalRouter->server);
	$testModul = $InternalRouter->test();
    $InternalRouter->stateEmitter->emit('ModuleTest:after', $testModul);
    
 
 
}, 'test#home');


$router->map('GET','/test/[o:oid]/', function($params = null) use(&$InternalRouter){
	  $oid = (is_array($params)) ? $params['oid'] : $params;
	 
	 $InternalRouter->stateEmitter->emit('test', array('test#item', $item));
	 
	  $testResult = $InternalRouter->test($oid);
	  
	echo print_r($testResult, true).PHP_EOL;
	
	
	  $InternalRouter->stateEmitter->emit('ModuleTest:test#item', array(
	     'item' => $item,
	     'result' => $testResult,
		  'oid' => $oid,
	  ));
	  
}, 'test#item');







if (version_compare(PHP_VERSION, '5.5') >= 1) {
     $CompletedTest = $InternalRouter->stateEmitter->required(['config', 'run', 'test'], $callback, false);
}else{
    $CompletedTest = $InternalRouter->stateEmitter->required(array('config', 'run', 'test'), $callback, false);
}







$match = $router->match();
if($InternalRouter->blog){
	$blog=$InternalRouter->getSiteBlog($blog);
}else{
	$blog = false;
}



