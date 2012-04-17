<?php defined('SYSPATH')or die('No direct access allowed.');

class Controller_Facebook extends Controller_Template{

	public $template='facebook';
	public $style='style';
	public $login_required=true;
	public $extended_permissions=NULL;

	public function before(){
		parent::before();
		$this->response->headers("Cache-Control: no-cache, must-revalidate");
		$this->response->headers("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		$this->response->headers('P3P', "CP=\"IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT\"");
		Session::instance();
		FB::instance();

		if($this->login_required){
			$login=Profiler::start('fb', 'login');
			FB::require_login($this->extended_permissions);
			Profiler::stop($login);

			FB::instance()->me=$this->_cached('_get_me_from_facebook', 3600, false, true);
			FB::instance()->protocol=$this->protocol();

			if(isset($_GET['request_ids'])){
				$case_id=$this->finishRequest();
			}

			$this->_cached('_save_me_as_user_in_database', 3600, false, true);
		}

		$this->canvas=View::factory($this->canvas);
		$this->template->bind('style', $this->style);
		$this->template->bind('canvas', $this->canvas);
	}

	public function after(){
		parent::after();
	}

	public function protocol(){
		if(isset($_SERVER['HTTPS'])&&
				($_SERVER['HTTPS']=='on'||$_SERVER['HTTPS']==1)||
				isset($_SERVER['HTTP_X_FORWARDED_PROTO'])&&
				$_SERVER['HTTP_X_FORWARDED_PROTO']=='https'){
			$protocol='https://';
		}
		else{
			$protocol='http://';
		}
		return $protocol;
	}

	public function friends_using_app(){
		$request=array(
			'method'=>'fql.query',
			'query'=>'select uid, name, is_app_user from user where uid in (select uid2 from friend where uid1=me()) and is_app_user=1'
		);
		return FB::$graph->api($request);
	}

	public function do_like(){
		$request=array(
			'method'=>'fql.query',
			'query'=>'SELECT page_id FROM page_fan WHERE uid = me() AND page_id = '.FB::$config['fanpage_id']
		);
		return (bool)FB::$graph->api($request);
	}

	public function tab_jump(){
		if(!empty(FB::$config['fanpage_name']))
			$url='https://www.facebook.com/'.FB::$config['fanpage_name'].'?sk=app_'.FB::$config['appId'];
		else
			$url='https://www.facebook.com/'.FB::$config['fanpage_id'].'?sk=app_'.FB::$config['appId'];
		echo "<script type='text/javascript'>top.location.href = '".$url."';</script>";
		echo '<p>Powinieneś zostać automatycznie przekierowany, jeśli do tego nie doszło, kliknij <a href="'.$url.'" target="_top">tutaj</a></p>';
		exit;
	}

	public function in_arrayr($needle, $array){
		if(!is_array($array))
			return false;
		$it=new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
		foreach($it AS $element)
			if($element==$needle)
				return true;
		return false;
	}

	private function _cache_set($cache_name, $data, $time=3600){
		if(isset(Cache::$instances['file']))
			$cache=Cache::$instances['file'];
		else
			$cache=Cache::instance('file');
		Cache::instance()->set($cache_name, $data, $time);
	}

	private function _cache_get($cache_name){
		if(isset(Cache::$instances['file']))
			$cache=Cache::$instances['file'];
		else
			$cache=Cache::instance('file');
		return Cache::instance()->get($cache_name);
	}

	/**
	 * ustawia cache dla wybranej metody
	 * @param string $method nazwa cachowanej metody
	 * @param int $time czas cache
	 * @param mixed $attr wartość przekazywana do cachowanej metody
	 * @param boolean $user_unique cache dla danej sesji lub globalne
	 * @param boolean $force_refresh odswiezenie cache
	 * @return string
	 * @todo obsługa dowolnej ilości parametrów
	 */
	protected function _cached($method, $time=10, $attr=false, $user_unique=false, $force_refresh=true){
		$cache_name=$method;
		if($user_unique)
			$cache_name=$cache_name.'_usr'.FB::$graph->getUser();
		if($attr!==false)
			$cache_name=$cache_name.'_attr'.$attr;

		if(($data=$this->_cache_get($cache_name))&&($force_refresh)){
			//echo 'used cache: ' . $cache_name . '<br />';
			return $data;
		}
		else{
			if($attr===false){
				$cache=$this->$method();
				$this->_cache_set($cache_name, $cache, $time);
				return $cache;
			}
			else{
				$cache=$this->$method($attr);
				$this->_cache_set($cache_name, $cache, $time);
				return $cache;
			}
		}
	}

	protected function _get_me_from_facebook(){
		$profiler=Profiler::start('fb', 'me');
		$me=FB::$graph->api('/me');
		Profiler::stop($profiler);
		return $me;
	}

	/**
	 *  sortuje alfabetycznie wg imienia
	 */
	protected function _get_friends(){
		$temp=FB::$graph->api('/me/friends');
		$friends=array();
		foreach($temp['data'] as $friend){
			$friends[strtr($friend['name'], 'ĘÓĄŚŁŻŹŃęóąśłżźćń', 'EOASLZZCNeoaslzzcn').$friend['id']]=$friend;
		}
		asort($friends);
		return $friends;
	}

	protected function _username($fbid){
		$this->_cached('_get_user_name_by_fb_id', 3600, $fbid);
	}

	protected function _get_user_name_by_fb_id($fbid){
		$temp=FB::$graph->api('/me/friends');
		$user=$temp['name'];
		return $user->name;
	}

	public function wall_post($friend_fb_id, $title, $description, $link, $img){
		$zapytanie=array(
			'name'=>$title,
			'caption'=>$description,
			'picture'=>$img,
			'link'=>$link,
		);
		FB::$graph->api("/$friend_fb_id/feed", 'post', $zapytanie);
	}

	protected function _save_me_as_user_in_database(){
		$user=ORM::factory('user', FB::instance()->me['id']);
		$user->id=FB::instance()->me['id'];
		$user->name=FB::instance()->me['name'];
		$user->gender=FB::instance()->me['gender'];
		$user->first_name=FB::instance()->me['first_name'];
		$user->last_name=FB::instance()->me['last_name'];
		$user->email=FB::instance()->me['email'];
		try{
			$user->save();
		}
		catch(Exception $e){
			return false;
		};
		return true;
	}

	private function finishRequest(){
		if(isset($_GET['request_ids']))
			$number=(int) $_GET['request_ids'];
		else
			$number=false;

		if(($number>10||$place=='messages')&&(isset($_REQUEST["signed_request"]))){
			$token_url="https://graph.facebook.com/oauth/access_token?"."client_id=".FB::$config['appId']."&client_secret=".FB::$config['secret']."&grant_type=client_credentials";

			$access_token=file_get_contents($token_url);

			$signed_request=$_REQUEST["signed_request"];

			list($encoded_sig, $payload)=explode('.', $signed_request, 2);
			$data=json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
			$user_id=$data["user_id"];

			$request_url="https://graph.facebook.com/".$user_id."/apprequests?".$access_token;
			$requests=file_get_contents($request_url);
			$req=$requests;
			$requests=explode("_", $requests);
			$requests=$requests[0];
			$requests=explode('id":', $requests);
			try{
				$requests=$requests[1];
			}
			catch(Exception $e){
				
			}
			$requests=str_replace('"', '', $requests);
			$data=json_decode($req);
			$last_id=0;
			foreach($data->data as $item){

				$fb_id=$item->to->id;
				if($fb_id==FB::instance()->me['id']){
					$id=$item->id;
					$delete_url="https://graph.facebook.com/".$id."?".$access_token."&method=delete";
					try{
						$result=file_get_contents($delete_url);
					}
					catch(Exception $e){
						
					}
					$last_id=(int) $id;
				}
			}
			$case=ORM::factory('request')->where('request_id', '=', $number)->find();
			return $case->case_id;
		}
	}
}
