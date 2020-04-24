<?php

$host = '0.0.0.0';
$port = 1001;
$http = new swoole_http_server($host, $port);

//设置配置
$http->set([
   'enable_static_handler' => true, //开启静态资源的访问
   'document_root' => '/home/test/public/static/', //设置静态资源的根目录
   'worker_num' => 4
]);

//设置一个在Worker开启时的回调函数
$http->on('WorkerStart', function(swoole_server $server, $worker_id) {
	
	 define('IS_SWOOLE', true);
	 //定义应用目录
	 define('APP_PATH', __DIR__ . '/../application/');
	 
	 //加载框架里面的文件
    require __DIR__ . '/../thinkphp/base.php';
    
    
});

//设置一个请求的回调函数
$http->on('request', function($request, $response) use($http) {
    $_SERVER = [
        'argv' => [],
    ];
    if(isset($request->server)) {
    	foreach($request->server as $k => $v) {
    		$_SERVER[strtoupper($k)] = $v;
    	}
    }
    if(isset($request->header)) {
    	foreach($request->header as $k => $v) {
    		$_SERVER[strtoupper($k)] = $v;
    	}
    }
    $_GET = [];
    if(isset($request->get)) {
    	foreach($request->get as $k=>$v){
    		$_GET[$k] = $v;
    	}
    }
    if(isset($request->server['path_info'])){
	$_GET['s'] = $request->server['path_info'];
    }
    $_POST = [];
    if(isset($request->post)) {
    	foreach($request->post as $k=>$v){
    		$_POST[$k] = $v;
    	}
    }
    ob_start();
    try {
        	echo think\Facade::make('app', [APP_PATH])->run()->send();
    } catch (\Exception $e) {
    	//...
	echo $e->getMessage() . "<br />";
	echo $e->getFile() . "<br />";
	echo $e->getLine() . "<br />";
    }
    //echo $request->app()->action().PHP_EOL;
    $result = ob_get_contents();
    ob_end_clean();
    $response->end($result);
    //$http->close();
});

$http->start();


