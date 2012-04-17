<?php defined('SYSPATH') OR die('No direct access allowed.');

class FB{
	/* Facebook driver instance */

	protected static $instances=array();

	/* Facebook SDK object */
	public static $graph;

	/* Config data */
	public static $config=array();

	/* Create instance of the driver */

	public static function instance($type = 'default', $config = array()){
		if(!isset(self::$instances[$type])OR!(self::$instances[$type] instanceof self)){
			self::$config=array_merge(/* Kohana::config('facebook') */ Kohana::$config->load('facebook')->as_array(), $config);
			self::$instances[$type]=new self();
		}
		return self::$instances[$type];
	}

	/* Prevent from direct object construction */

	final private function __construct(){
		self::$graph=new Facebook(array(
					'appId'=>self::$config['appId'],
					'secret'=>self::$config['secret'],
					'cookie'=>self::$config['cookie'],
					'domain'=>self::$config['domain'],
					'fileUpload'=>self::$config['fileUpload'],
				));
	}

	/* Prevent from cloning  */

	final private function __clone(){
		
	}

	/* Requires Facebook login and valid session */

	public static function require_login($extended_permissions = NULL, $next = NULL){
		if(!empty(self::$config['basic_permissions'])AND!empty($extended_permissions)){
			$extended_permissions=self::$config['basic_permissions'].','.$extended_permissions;
		}
		else if(empty($extended_permissions)){
			$extended_permissions=self::$config['basic_permissions'];
		}

		$user=self::$graph->getUser();
		
		if($user&&!empty($extended_permissions)){
			$request=array(
				'method'=>'fql.query',
				'query'=>'SELECT '.$extended_permissions.' FROM permissions WHERE uid = me()'
			);
			try{
				$result=self::$graph->api($request);
				if(isset($result[0])){
					foreach($result[0] as $perm){
						if($perm==='0'){
							$acces_danied=TRUE;
							break;
						}
					}
				}
			}
			catch(FacebookApiException $e){
			}
		}
		
		if(isset($acces_danied)||!$user){
			$loginUrl=self::$graph->getLoginUrl(array(
				'display'=>'page',
				'redirect_uri'=>'http://apps.facebook.com/'.self::$config['canvas_url'].'/'.($next ? $next : Request::current()->uri().URL::query()),
				'scope'=>$extended_permissions,
					));
			echo "<script type='text/javascript'>top.location.href = '$loginUrl';</script>";
			echo '<p>Powinieneś zostać automatycznie przekierowany, jeśli do tego nie doszło, kliknij <a href="'.$loginUrl.'" target="_top">tutaj</a></p>';
			exit;
		}
	}

	public static function token_app(){
		$token_url="https://graph.facebook.com/oauth/access_token?".
				"client_id=".self::$config['appId'].
				"&client_secret=".self::$config['secret'].
				"&grant_type=client_credentials";

		$app_access_token=file_get_contents($token_url);

		return $app_access_token;
	}

	public static function apprequest_url($user_id, $msg, $data){
		$apprequest_url="https://graph.facebook.com/".
				$user_id.
				"/apprequests?message=".urlencode($msg).
				"&data=".urlencode($data)."&".
				self::token_app()."&method=post";

		$result=file_get_contents($apprequest_url);
		return $result;
	}

}
