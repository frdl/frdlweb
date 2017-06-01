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
namespace frdlweb\Endpoint;
use frdl\Flow\EventEmitter as EventEmitter;
use frdlweb\Storage\Pdo as Pdo;
/**
* @frdl http://frdl.de
* @webfan https://webfan.de
*/

abstract class MultiRouter extends Router
{
	/**
	*   Awesome
	*   $stateEmitter
	*
	*   @awesome
	*/		
    public $stateEmitter = false;
	public $db = false;	
	public $blog = false;	
	public $BLOGHOST = false;	
	
	/**
	*   $tpl
	*
	*   @~development @Helper @TemplateVar
	*/	
	public $tpl = array();
	
	
	/**
	*   $tpl_main_content_html
	*
	*   @~development @Helper @TemplateVar
	*/	
	public $tpl_main_content_html = '';
	
	function __construct($options = array()){
	    parent::__construct();
		$this->tpl['document.title'] = $_SERVER['HTTP_HOST'];
		$this->stateEmitter = new EventEmitter();
		$this->init($options);
	}
	
	/**
	*   Test
	*
	*   @Test
	*/		
	abstract public function test();
	
	/**
	*   e.g.: return  \webfanStateEmitter::$emitter;
	*          mutex/singleton/parentContext ... 
	*/
	abstract public function parentEmitter(); 
	
	/**
	*   Require Authentication
	*
	*   e.g.: user/login/auth -Content to protect ...
	*/	
	abstract public function secure_login($options = array());
	
	/**
	*   Map Next Routes
	*
	*   e.g.: setBasePath
	*           ->setBasePath()
	*           ->map()
	*
	*     ...
	*/	
	abstract public function init($options = array());

	/**
	*   Homepagesystem
	*
	*   @BindApplicationToHost
	*   @Proprietary
	*/	
	abstract protected function _setBLOGHOST($blog = false);
	abstract public function setSiteBlogById($blog_id = null);
	abstract public function setSiteBlogBySubdomain($subdomain = null);
	final public function getSiteBlog(&$blog = null){
		$blog = &$this->blog;
		return $this->blog;
	}
	
	
	
	
	public function __get($name){
	    if('Router' === $name){
		   $THAT = &$this;		
		   return $THAT;	
		}
		
		return null;
	}
	
	

	public function set_db(&$DB = null ){
		if(!is_object($DB)){
			$this->db = new Pdo();
			$DB = &$this->db;
		}else{
			$this->db = $DB;
		}
		
		return $this;
	}
}