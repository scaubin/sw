<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------


namespace app\Console;

use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * Class Output
 * @package think\console
 *
 * @see     \think\console\output\driver\Console::setDecorated
 * @method void setDecorated($decorated)
 *
 * @see     \think\console\output\driver\Buffer::fetch
 * @method string fetch()
 *
 * @method void info($message)
 * @method void error($message)
 * @method void comment($message)
 * @method void warning($message)
 * @method void highlight($message)
 * @method void question($message)
 */
class HttpServer extends Command
{
    

   // 命令行配置函数
    protected function configure()
    {
        // setName 设置命令行名称
        // setDescription 设置命令行描述
        $this->setName('HttpServer:start')->setDescription('Start Http Server!');
    }

    // 设置命令返回信息
    protected function execute(Input $input, Output $output)
    {   
         // 监听所有地址，监听 10000 端口
        $this->server = new \swoole_http_server('0.0.0.0', 9501);
        
        // 设置 server 运行前各项参数
        // 调试的时候把守护进程关闭，部署到生产环境时再把注释取消
        // $this->server->set([
        //     'daemonize' => true,
        // ]);
        
        // 设置回调函数
        $this->server->on('Request', [$this, 'onRequest']);
        $this->server->start();
    }


    // 建立连接时回调函数
    public function onRequest(\swoole_http_server $server, \swoole_http_server $response)
    {
         $data = isset($request->get) ? $request->get : '';
         $response->end(serialize($data));     
    }



    // 连接关闭时回调函数
    public function onClose($server, $fd)
    {
        echo "client {$fd} closed\n";
    }

}
