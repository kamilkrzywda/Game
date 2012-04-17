<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>IFrame Base Facebook Application Development</title>

		<script type="text/javascript">
			var fbUtils = { 
			    iframeSize : function(width,height) {
	                var obj    = new Object;
	                obj.width  = width;
	                obj.height = height;
	                FB.Canvas.setSize(obj);
	            },
            	autoSize : function() {
	            	FB.Canvas.setAutoResize();
	            }
            }
		</script>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
		<!--[if lt IE 8]><link rel="stylesheet" href="/media/css/ie7.css" /><![endif]-->
		<!--[if lt IE 9]>
			<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
			<script type="text/javascript" src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
			<script type="text/javascript" src="/media/js/selectivizr.js"></script>
		<![endif]-->
	</head>
	<body class="<?= Request::current()->action() ?>">
		<div id="canvas">
			<?=$canvas?>
		</div>	
		<div id="fb-root"></div>
		<script type="text/javascript">
			window.fbAsyncInit = function() {
	        	FB.init({
	          		appId  : '<?=FB::$config['appId']?>',
	          		status : <?=FB::$config['status']?>,
	          		cookie : <?=FB::$config['cookie']?>,
	          		xfbml  : <?=FB::$config['xfbml']?>
	        	});
	        	fbUtils.autoSize();
    		};
    		(function() {
    			var e = document.createElement('script');
    			e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
    			e.async = true;
    			document.getElementById('fb-root').appendChild(e);
    		}());
    		
		</script>
	</body>
</html>