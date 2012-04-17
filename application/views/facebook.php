<?php defined('SYSPATH') or die('No direct access allowed.'); ?><!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="https://www.facebook.com/2008/fbml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="/media/css/reset.css" />
		<link rel="stylesheet" type="text/css" href="/media/css/<?= $style ?>.css" />
		<meta property="fb:app_id" content="<?= FB::$config['appId'] ?>" />
		<?php if(isset($headers)){ ?>
			<meta property="og:title" content="<?= $headers['title'] ?>" /> 
			<meta property="og:type" content="website" />
			<meta property="og:description" content="<?= $headers['desc'] ?>" /> 
			<meta property="og:url" content="<?= $headers['url'] ?>" />
		<?php } ?>
		<title>Wasa</title>

		<script type="text/javascript">
			var fbUtils = { 
				iframeSize : function(width,height) {
					var obj    = new Object;
					obj.width  = width;
					obj.height = height;
					FB.Canvas.setSize(obj);
				}
			}
		</script>
		<script type="text/javascript" src="/media/js/jquery.min.js"></script>
		<script type="text/javascript" src="/media/js/script.js"></script>
		<!--[if lt IE 9]>
			<script src="//ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
			<script type="text/javascript" src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body class="<?= Request::current()->action() ?>">
		<div id="fb-root"></div>
		<script type="text/javascript">
			window.fbAsyncInit = function() {
				FB.init({
					appId  : '<?= FB::$config['appId'] ?>',
					status : <?= FB::$config['status'] ?>,
					cookie : <?= FB::$config['cookie'] ?>,
					xfbml  : <?= FB::$config['xfbml'] ?>,
					frictionlessRequests : true
				});
        FB.Canvas.setAutoGrow(true);
				FB.Event.subscribe('edge.create',
				function(response) {
						$('#polub').fadeOut('fast');
				});
			};
			(function(d){
				 var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
				 js = d.createElement('script'); js.id = id; js.async = true;
				 js.src = "//connect.facebook.net/en_US/all.js";
				 d.getElementsByTagName('head')[0].appendChild(js);
			 }(document));
		</script>
		<div id="canvas">
			<?= $canvas ?>
		</div>
	</body>
</html>