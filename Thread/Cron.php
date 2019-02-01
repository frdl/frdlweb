<?php
/*
Copyright (c) 2019 Webfan Homepagesystem
*/
namespace frdlweb\Thread;
class Cron
{
	
	const PFX = '@CRONJOB::';
	
	
	protected $blog = null;
	
	public function __construct($id_blog){
		$this->blog = \wBLOG_HELPER::getBlogDataById($id_blog, true);
		
		if(!is_array($this->blog)){
			    throw new \Exception('Cannot load site in '.basename(__FILE__).' '.__LINE__);
		}		
	}
	
	public function k($job){
		return 'last_time'.' '.self::PFX.$job['name'];
	}
	
	public static function cronKey($name){
		return 'last_time'.' '.self::PFX.$name;
	}
	
	public function s(){
		return 'last_time'.' '.self::PFX.'*';
	}
	
	
	public function getJobs($jobs = []){
		
	  $jobs[]=[
		'name' => 'maintenance repair (report)', 
	    'interval' => 1 * 24 * 60 * 60,
		'label' => 'Detect problems and report',	
		'cmd' => 'hps.maintenance.repair',
	    'params' =>	[
			[
	       'solveProblems' => \Webfan::isTrue(\BLOGSETTINGS_HANDLER::getMutex()->getVar($this->blog['id'], 'hps_autorepair', true, 'true') ),	
	    ],
		],	
	  ];
			
		
		
		
		
		
		
	$last_times = \BLOGSETTINGS_HANDLER::getMutex()->select($this->s(), $this->blog['id']);	
	
	   $ClearAllCachesJob=[
		'name' => 'clear caches', 
	    'interval' => 12 * 31 * 24 * 60 * 60,
		'label' => 'Clear caches',	
		'cmd' => 'cache.clear',
			
	   ];	   
	      
		
			if(!isset($last_times[$this->k($ClearAllCachesJob)])
					  || intval($last_times[$this->k($ClearAllCachesJob)]) <  intval(\Webfan::i()->Config -> get('bootstrap.kernel.CACHE_FORCE_EXPIRE_TIME'))  ) {
				$jobs[]=$ClearAllCachesJob;
			}
		
		  	
		
		
		
	   $jobs[]=[
		'name' => 'recompile container', 
	    'interval' => 7 * 24 * 60 * 60,
		'label' => 'Recompile container',	
		'cmd' => 'hps.container.check',
              'params' =>	
			   [
	            'forceCompile' =>  \Webfan::isTrue(\BLOGSETTINGS_HANDLER::getMutex()->getVar($this->blog['id'], 'hps_autorepair', true, 'true') )
				   && \Webfan::isTrue(\BLOGSETTINGS_HANDLER::getMutex()->getVar($this->blog['id'], 'hps_autoupdate_modules', true, 'true') ),	
	          ],
		    
			
	   ];	 
		
		
		$jobs[]=[
		'name' => 'prune container', 
	  //  'interval' => 1 * 24 * 60 * 60,
		'interval' => 7 * 24 * 60 * 60,
		'label' => 'Prune Container',	
		'cmd' => 'hps.maintenance.prune',
	    'params' =>	
			[
	            'json_rpc' => false,
	            'templates' => false,
		        'routes' => false,
		        'modules' => false,
		        'accounting' => false,
		        'hps' => false,	
		        'container' =>  \Webfan::isTrue(\BLOGSETTINGS_HANDLER::getMutex()->getVar($this->blog['id'], 'hps_autoupdate_modules', true, 'true') ),	
	    ]
		
			
	  ];	
		
   if(\Webfan::isTrue(\BLOGSETTINGS_HANDLER::getMutex()->getVar($this->blog['id'], 'hps_autoupdate_modules', true, 'true') )){	
	 
	
	   
	   $jobs[]=[
		'name' => 'maintenance autoupdate', 
	    'interval' => 2 * 60 * 60,
		'label' => 'Update modules',	
		'cmd' => 'hps.autoupdate',
	   			
	  ];	
	   
	   
	   		
	  $jobs[]=[
		'name' => 'patch fixtures', 
	    'interval' => 24 * 60 * 60,
		'label' => 'Patch fixtures',	
		'cmd' => 'hps.patch.fixtures',
	   			
	  ];
	   
	   
	    $jobs[]=[
		'name' => 'recompile events', 
	    'interval' => 7 * 24 * 60 * 60,
		'label' => 'Recompile events',	
		'cmd' => 'hps.recompile.events',
			
	   ];
	   
	   
	     
	   
   }//	if(\Webfan::isTrue(\BLOGSETTINGS_HANDLER::getMutex()->getVar($this->blog['id'], 'hps_autoupdate_modules', true, 'true') ) ){	
		
	
	$jobs[]=[
		'name' => 'prune system cache templates', 
	     'interval' => 7 * 24 * 60 * 60,
		'label' => 'Prune system cache templates',	
		'cmd' => 'hps.maintenance.prune',
	    'params' =>	
			[
	            'json_rpc' => false,
	            'templates' => true,
		        'routes' => false,
		        'modules' => false,
		        'accounting' => false,
		        'hps' => false,	
		        'container' => false,	
	    ]
		
			
	  ];			
		
		
	
	$jobs[]=[
		'name' => 'prune system cache routes', 
	     'interval' => 24 * 60 * 60,
		'label' => 'Prune system cache routes',	
		'cmd' => 'hps.maintenance.prune',
	    'params' =>	
			[
	            'json_rpc' => false,
	            'templates' => false,
		        'routes' => true,
		        'modules' => false,
		        'accounting' => false,
		        'hps' => false,	
		        'container' => false,	
	    ]
		
			
	  ];			
		
		
	$jobs[]=[
		'name' => 'prune system cache accounting', 
		'interval' =>  24 * 60 * 60,
		'label' => 'Prune system cache accounting',	
		'cmd' => 'hps.maintenance.prune',
	    'params' =>	
			[
	            'json_rpc' => false,
	            'templates' => false,
		        'routes' => false,
		        'modules' => false,
		        'accounting' => true,
		        'hps' => false,	
		        'container' => false,	
	    ]
		
			
	  ];
				
		
	$jobs[]=[
		'name' => 'prune system cache modules', 
		'interval' =>  24 * 60 * 60,
		'label' => 'Prune system cache modules',	
		'cmd' => 'hps.maintenance.prune',
	    'params' =>	
			[
	            'json_rpc' => false,
	            'templates' => false,
		        'routes' => false,
		        'modules' => true,
		        'accounting' => false,
		        'hps' => false,	
		        'container' => false,	
	    ]
		
			
	  ];
		
		
	$jobs[]=[
		'name' => 'prune system cache hps', 
	  //  'interval' => 1 * 24 * 60 * 60,
		'interval' => 60,
		'label' => 'Prune system cache hps',	
		'cmd' => 'hps.maintenance.prune',
	    'params' =>	
			[
	            'json_rpc' => false,
	            'templates' => false,
		        'routes' => false,
		        'modules' => false,
		        'accounting' => false,
		        'hps' => true,	
		        'container' => false,	
	    ]
		
			
	  ];			
		
		return $jobs;
	}
	
	/**
\webfan\hps\Api\Response\Format::RESULT
\webfan\hps\Api\Response\Format::ARRAY
\webfan\hps\Api\Response\Format::ASSOC
*/
	
	public function __invoke( $callback = null, $format = \webfan\hps\Api\Response\Format::ASSOC){
				
		$last_times = \BLOGSETTINGS_HANDLER::getMutex()->select($this->s(), $this->blog['id']);
		
		
		$Impersonated = new \webfan\hps\Api\Impersonated($this->blog['id']);		
		//$Impersonated->debug = true;
		$client =$Impersonated->getClient();			                   
		$client->batch();
		
		$results = [];
		$i = 0;
		
		$jobResults = [];
		
		foreach($this->getJobs() as $num => $job){
	
				 $cmd = $job['cmd'];		
				
			if( !isset($last_times[$this->k($job)]) || intval($last_times[$this->k($job)]) < time() - intval($job['interval']) ){
				$i++;
			//	$id = $i + 1;
			//	$id = null;
				
				
				\BLOGSETTINGS_HANDLER::getMutex()->setVar($this->blog['id'], $this->k($job), time());				
				// $client->{'hps.container.check'}();			
				if(!isset($job['params'])){				
					$Request = \webfan\hps\Api\Request\RequestBuilder::create(null, []);
				//	call_user_func_array([$client, $cmd], [\webfan\hps\Api\Request\RequestBuilder::create($id, [])]);
				}else{
					$Request = \webfan\hps\Api\Request\RequestBuilder::create(null, $job['params']);
				//	call_user_func_array([$client, $cmd], [\webfan\hps\Api\Request\RequestBuilder::create($id, $job['params'])]);
				}	
				
				$jobResults[$Request->id()] = [
					    'name'=> $job['name'],
					    'id' => $Request->id(),
					    'hit' => true,
					];
				
				call_user_func_array([$client, $cmd], [$Request]);
			}else{
				$id = \webfan\hps\Api\Request\RequestBuilder::create(null, [])->id();
				$jobResults[$id] = [
					    'name'=> $job['name'],
					    'id' => $id,
					    'hit' => false,
					];				
			}
		}
		
		if($i > 0){
			try{
		     //  $results = $client->send(true);	
		     //  $results = $client->send(false);	
		     //  $results = $client->send('keys');	
				
		       //  $results = $client->send('keys');	
				 $results = $client->send(2);	
				
			}catch(\Exception $e){
			   //$results = $e->getMessage();	
				$results = null;
			}
		}
		
		
		if(is_array($results)){
			foreach($results as $id => $res){
				if($format === \webfan\hps\Api\Response\Format::ASSOC){
						$jobResults[$id]['result'] = $res;
				}elseif($format === \webfan\hps\Api\Response\Format::NUM){
						$jobResults[$res['id']]['result'] = $res;
				}
			
			}
		}
		
		if(is_callable($callback)){
		   call_user_func_array($callback, [$jobResults]);	
		}
		
		if($format === \webfan\hps\Api\Response\Format::RESULT)return $results;
		return $jobResults;
	}
		
	
	
	
}
