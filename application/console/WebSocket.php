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
use think\Db;
use think\Cache;
use think\Session
use think\Exception;

/**
 * Class Output
 * @package think\console
 *
 * @see     \think\console\output\driver\Console::setDecorated
 * @method void setDecorated($decorated)
 *
 */
class WebSocket extends Command
{
    
    protected $userPrex = 'uid_bind_fd_';  
    protected $fdPrex = 'fd_bind_uid_';  
   // 命令行配置函数
    protected function configure(){
        // setName 设置命令行名称
        // setDescription 设置命令行描述
        $this->setName('im:start')->setDescription('Start IM Server!');
    }

    // 设置命令返回信息
    protected function execute(Input $input, Output $output){   
         // 监听所有地址，监听 10000 端口
        $this->server = new \swoole_websocket_server('0.0.0.0', 9501);
        
        // 设置 server 运行前各项参数
        // 调试的时候把守护进程关闭，部署到生产环境时再把注释取消
        // $this->server->set([
        //     'daemonize' => true,
        // ]);
        
        // 设置回调函数
        $this->server->on('Open', [$this, 'onOpen']);
        $this->server->on('Message', [$this, 'onMessage']);
        $this->server->on('Close', [$this, 'onClose']);
        
        $this->server->start();
        $output->writeln("im: Start.\n");
    }


    // 建立连接时回调函数
    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request){
        //echo "server: handshake success with fd{$request->fd}\n";
    }

    // 收到数据时回调函数
    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame){
       // echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $server->push($frame->fd, "11111111111111111111111");
    }

    // 连接关闭时回调函数
    public function onClose(\swoole_websocket_server $server, $fd){
        	 //设置用户为登录状态  
    	    $fdkey = $this->fdPrex.$fd;
            $uid =  Cache::get($fdkey); 
            Db::name('user')->where('id', $uid)->update(['status' => 0]);
    }

    public  function daelMessage(\swoole_websocket_server $server,$fd, $data){
        $message = json_decode($data, true);
        $message_type = $message['type']; 
        switch ($message_type) {
        case 'init':
            $uid = $message['id'];
            // 设置session
   
            session('username', $message['username']);
            session('avatar', $message['avatar']);
            session('uid', $uid);

            // 将当前链接与uid绑定
            $fdkey = $this->fdPrex.$fd;
            $userkey = $this->userPrex.$uid;
            Cache::set($fdkey, $uid);
            Cache::set($userkey, $fd);
            //查询最近1周有无需要推送的离线信息
            $map['need_send'] = 1;
            $map['to_id'] = $uid;
            $map['type'] = 'friend';
            $resMsg = Db::name('chatlog')->where($map)->select();
            //var_export($resMsg); if (!empty($resMsg)) {

            foreach ($resMsg as $key => $vo) {
                $log_message = [
	                'message_type' => 'logMessage', 
	                'data' => [
	                    'username' => $vo['from_name'], 
	                    'avatar' => $vo['from_avatar'], 
	                    'id' => $vo['from_id'],
	                    'type' => 'friend',
	                    'content' => htmlspecialchars($vo['content']),
	                     'timestamp' => $vo['timeline'] * 1000,
	                    ]
	                ];

                $this->sendToUid($uid, json_encode($log_message));
                //设置推送状态为已经推送
                Db::name('chatlog')->where('id', $vo['id'])->update(['need_send' => 0]);
              
                }
            }
            
            //设置用户为登录状态    
            Db::name('user')->where('id', $uid)->update(['status' => 1]);
            break;
         case 'chatMessage':
			// 聊天消息
            $fdkey = $this->fdPrex.$fd;
            $uid =  Cache::get($fdkey);

			$content = $message['data']['to']['content'];
			$to_uid = $message['data']['to']['to_uid'];

			 
			$chat_message = [
			    'message_type' => 'chatMessage', 
				'data' => [
					'username' => $_SESSION['username'],
					'avatar' => $_SESSION['avatar'],
					'id' => type === 'friend' ? $uid : $to_id, 
					'type' => $type,
					'content' => htmlspecialchars($message['data']['mine']['content']),
					'timestamp' => time() * 1000,
				 
				]
			];
			// 加入聊天log表
			$param = [
			   'from_id' => $uid, 
			   'to_id' => $to_uid,
			   'from_name' => $_SESSION['username'], 
			   'from_avatar' => $_SESSION['avatar'],
			   'content' => htmlspecialchars($message['data']['mine']['content']),
			   'timeline' => time(),
			   'need_send' => 0
			];

			//用户不在线,标记此消息推送
			if (empty(Gateway::getClientIdByUid($to_id))) {
			   $param['need_send'] = 1;	
			   	 
			}else{
                $this->sendToUid($uid, json_encode($chat_message));     
            }
            
			Db::name('chatlog')->insert($param);
			
			break;
		}
    }

    public funciton sendToUid($uid, $log_message){
         $userkey = $this->userPrex.$uid;
         $fd = Cache::get($userkey);
         $this->server->push($fd, $log_message);
    }


}
