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
/**
* @frdl http://frdl.de
* @webfan https://webfan.de
*/

class Router extends \AltoRouter {
	/**
	 * @var array Array of default match types (regex helpers)
	 */
	protected $matchTypes = array(
		'i'  => '[0-9]++',     //INTEGER
		'a'  => '[0-9A-Za-z]++',   //ALPHANUMERIC
		'h'  => '[0-9A-Fa-f]++',   //HASH
		'*'  => '.+?',         //ANY OPTIONAL
		'**' => '.++',         //ANY
		
		'o' => '[0-9\.]++',   // OID		
		
		
		
		''   => '[^/\.]++',      //?VALID  --- SEGMENT/ROUTE/DIRECTORY/FOLDER
		
		
	);
	
	
	
	public $server = array();
	public $RoutingEvents = false;
	
	
	function __construct(){
	     parent::__construct();
		
		 $this->RoutingEvents =  new EventEmitter();
		
		
		 $p = explode(':', $_SERVER['HTTP_HOST']);
		 $_port = 80;
		 if(isset($p[1]) && is_numeric($p[1]))$_port = intval($p[1]);
		 $this->server['port'] = (isset($_SERVER['HTTP_PORT']))?$_SERVER['HTTP_PORT'] : $_port;
		 $this->server['base_uri'] = (isset($_SERVER['REQUEST_URI']))?$_SERVER['REQUEST_URI'] : '/';
		 $this->server['name'] = $p[0];
	
		 $h = explode('.', $this->server['name']);
		 $h = array_reverse($h);
		 $this->server['domain'] = (isset($h[1])) ? $h[1].'.'.$h[0] : $h[0];
		 $this->server['subdomain'] = (isset($h[2])) ? $h[2] : false;
		
		 $this->server['webfan_subdomain'] = (isset($h[1]) && 'webfan'===$h[1] && isset($h[2])) ? $h[2] : false;
		
		
		$THAT = &$this;
		$this->RoutingEvents->emit('base', $THAT);
	}
	
	
	/**
	 * Match a given Request Url against stored routes
	 * @param string $requestUrl
	 * @param string $requestMethod
	 * @return array|boolean Array with route information on success, false on failure (no match).
	 */
	public function match($requestUrl = null, $requestMethod = null, $execute = true) {

		$params = array();
		$match = false;

		// set Request Url if it isn't passed as parameter
		if($requestUrl === null) {
			$requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
		}

		// strip base path from request url
		$requestUrl = substr($requestUrl, strlen($this->basePath));

		// Strip query string (?a=b) from Request Url
		if (($strpos = strpos($requestUrl, '?')) !== false) {
			$requestUrl = substr($requestUrl, 0, $strpos);
		}

		// set Request Method if it isn't passed as a parameter
		if($requestMethod === null) {
			$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
		}

		foreach($this->routes as $handler) {
			list($methods, $route, $target, $name) = $handler;

			
			
			$method_match = (stripos($methods, $requestMethod) !== false);

			// Method did not match, continue to next route.
			if (!$method_match) continue;

			if ($route === '*') {
				// * wildcard (matches all)
				$match = true;
			} elseif (isset($route[0]) && $route[0] === '@') {
				// @ regex delimiter
				$pattern = '`' . substr($route, 1) . '`u';
				$match = preg_match($pattern, $requestUrl, $params) === 1;
			} elseif (($position = strpos($route, '[')) === false) {
				// No params in url, do string comparison
				$match = strcmp($requestUrl, $route) === 0;
			} else {
				// Compare longest non-param string with url
				if (strncmp($requestUrl, $route, $position) !== 0) {
					continue;
				}
				$regex = $this->compileRoute($route);
				$match = preg_match($regex, $requestUrl, $params) === 1;
			}

			if ($match) {

				if ($params) {
					foreach($params as $key => $value) {
						if(is_numeric($key)) unset($params[$key]);
					}
				}
				
				$r = false;
				if(is_callable($target)){
				  $r = call_user_func_array($target, $params);	
				}
				
				
				$THAT = &$this;
		        $this->RoutingEvents->emit('match', array(
		            'ctrl' => $THAT,
					'target' => $target,
					'params' => $params,
					'name' => $name,
					'result' => $r,	            
		        ));
				
				
				
				
               if(true === $r)break;	


				return array(
					'target' => $target,
					'params' => $params,
					'name' => $name
				);
			}
		}
		return false;
	}
	
	
	
	/**
	 * Compile the regex for a given route (EXPENSIVE)
	 */
	protected function compileRoute($route) {
		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {

			$matchTypes = $this->matchTypes;
			foreach($matches as $match) {
				list($block, $pre, $type, $param, $optional) = $match;

				if (isset($matchTypes[$type])) {
					$type = $matchTypes[$type];
				}
				if ($pre === '.') {
					$pre = '\.';
				}

				$optional = $optional !== '' ? '?' : null;
				
				//Older versions of PCRE require the 'P' in (?P<named>)
				$pattern = '(?:'
						. ($pre !== '' ? $pre : null)
						. '('
						. ($param !== '' ? "?P<$param>" : null)
						. $type
						. ')'
						. $optional
						. ')'
						. $optional;

				$route = str_replace($block, $pattern, $route);
			}

		}
		return "`^$route$`u";
	}
	
}
