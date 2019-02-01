<?php
/*
MIT License
Copyright (c) 2019 Webfan Homepagesystem
*/
namespace frdlweb\Thread;
class ShutdownTasks {
    protected $callbacks; 
    protected static $instance = null; 
    public function __construct() {
        $this->callbacks = [];
		register_shutdown_function(array($this, 'callRegisteredShutdown'));
    }
	
	public function __invoke(){
		return call_user_func_array(array($this,'registerShutdownEvent'), func_get_args() ); 
	}
	
	public function __call($name, $params){
		if('clear'===$name){
			$this->callbacks = [];
			return $this;
		}
		
		throw new \Exception('Unhandled metod in '.__METHOD__.' '.basename(__FILE__).' '.__LINE__);
	}	
	
	public static function __callStatic($name, $params){
		return call_user_func_array(array(self::mutex(),$name), $params ); 
	}
	
	
    public static function mutex() {
             if(null===self::$instance){
			    	self::$instance = new self; 
			 }
		
		return self::$instance;
    }
	
    public function registerShutdownEvent() {
        $callback = func_get_args();
       
        if (empty($callback)) {
            trigger_error('No callback passed to '.__FUNCTION__.' method', E_USER_ERROR);
            return false;
        }
        if (!is_callable($callback[0])) {
            trigger_error('Invalid callback passed to the '.__FUNCTION__.' method', E_USER_ERROR);
            return false;
        }
        $this->callbacks[] = $callback;
		
		if(0===count($this->callbacks)){
				register_shutdown_function(array($this, 'callRegisteredShutdown'));
		}
        return true;
    }
    public function callRegisteredShutdown() {
		while(0<count($this->callbacks)){
		  	$arguments = array_shift($this->callbacks);
			$callback = array_shift($arguments);
		    call_user_func_array($callback, $arguments);
		}
    }
}
