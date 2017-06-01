<?php
/**
 * 
 * Copyright  (c) 2017, Till Wehowski
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this software
 *    must display the following acknowledgement:
 *    This product includes software developed by the frdl/webfan.
 * 4. Neither the name of frdl/webfan nor the
 *    names of its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY frdl/webfan ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL frdl/webfan BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 */
namespace frdlweb\Storage;
/**
* @frdl http://frdl.de
* @webfan https://webfan.de
*/

class Pdo implements idb 
{
  protected $PDO = false;
  protected static $mutex = null;	
	
  final public static function getDbAdapter(){
	  if(null === self::$mutex){
		  self::$mutex = &$this->PDO;
	  }
	  
	  return self::$mutex;
  }
	
  public function prepare ( string $statement , array $driver_options = array() ){
	 return call_user_func_array(array($this->PDO, 'prepare'), array($statement, $driver_options) );
  }
	
  public function query ( string $statement ){
	 return call_user_func_array(array($this->PDO, 'query'), array($statement) );
  }
	
  function __construct($PDO = null, $dns = null){
	  if(null == $PDO){
		$this->PDO = new \PDO($dns, array());  
	  }else{
		 $this->PDO = $PDO;   
	  }
  }
  public function __get($name){
	  if('DB' === $name){
		 return $this->PDO;
	  }
	
	  return null;
  }	
	
  public function __set($name, $params){
	  if('DB' === $name){
		$this->PDO = $DB;  
	  }
  }	
	
  public function __call($name, $params){
	   if(is_callable(array($this->PDO, $name))){
		  return call_user_func_array(array($this->PDO, $name), $params);
	   }
  }
	
}