<?php defined('SYSPATH') OR die('No direct access allowed.');

require_once (Kohana::find_file('vendors', 'Facebook/facebook'));
	
//Route::set('facebook', '(<action>(/<id>)(/p<page>))',array('id'=>'[0-9]+'))
//	->defaults(array(
//		'controller' => Kohana::$config->load('facebook.controller'),//Kohana::config('facebook')->get('controller'),
//		'action'     => Kohana::$config->load('facebook.action'),//Kohana::config('facebook')->get('action'),
//	));
