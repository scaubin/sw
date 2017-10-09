<?php
namespace app\Console;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Cache;
use think\Exception;

class TcpServer extends Command
{
    protected $server;

    // 命令行配置函数
    protected function configure()
    {
        // setName 设置命令行名称
        // setDescription 设置命令行描述
        $this->setName('task:start')->setDescription('Start task Server!');
    }

    // 设置命令返回信息
    protected function execute(Input $input, Output $output)
    {
        $this->server = new \swoole_server('0.0.0.0', 9501);

        $this->server->set([
            'worker_num' => 4,
            'daemonize'  => false,
            'task_worker_num' => 4  # task 进程数
        ]);

        $this->server->on('Start', [$this, 'onStart']);
        $this->server->on('Connect', [$this, 'onConnect']);
        $this->server->on('Receive', [$this, 'onReceive']);
        $this->server->on('Task', [$this, 'onTask']);
        $this->server->on('Finish', [$this, 'onFinish']);
        $this->server->on('Close', [$this, 'onClose']);

        $this->server->start();
        // $output->writeln("TCP: Start.\n");
    }

    // 主进程启动时回调函数
    public function onStart(\swoole_server $serv)
    {
        echo "task Start\n";
    }

    // 建立连接时回调函数
    public function onConnect(\swoole_server $server, $fd, $from_id)
    {
        echo "task Connect\n";
    }

    // 收到信息时回调函数
    public function onReceive(\swoole_server $server, $fd, $from_id, $data)
    {
        echo "message: {$data} form Client: {$from_id} \n";
        // 将受到的客户端消息再返回给客户端
        Cache::set('from',$fd,3600);
        $task_id = $server->task($data);
        $server->send($fd, "Message form Server: {$data}， task_id: {$task_id}");
    }

     // 异步任务处理函数
    public function onTask(\swoole_server $server, $task_id, $from_id, $data)
    {
        //返回任务执行的结果
        $server->finish("$data");
    }   

    // 异步任务完成通知 Worker 进程函数
    public function onFinish(\swoole_server $server, $task_id, $data)
    {
        $info = json_decode($data,true);
        $updata['user_id'] = $info['id'];
        $updata['addtime'] = $info['time'];
        //$redis = new \Redis();
       // $redis->connect('127.0.0.1', 6379);
        //$redis->set('key' . $data, 1, $exp);
        $fd = Cache::get('from');
        echo 'fd:'.$fd;
        $res = Db::name('topic')->insert($updata);

    }


    // 关闭连时回调函数
    public function onClose(\swoole_server $server, $fd, $from_id)
    {
        echo "Close\n";
    }
}