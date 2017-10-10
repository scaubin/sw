<?php
namespace app\index\controller;
use think\Controller;
class Im extends Controller
{
    public function index()
    {
         return $this->fetch('kefu');
    }
}
