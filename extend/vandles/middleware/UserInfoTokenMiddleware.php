<?php
/*
 * 
 * Author: vandles
 * Date: 2022/7/26 9:50
 * Email: <vandles@qq.com>
 */


namespace vandles\middleware;




use vandles\lib\Request;
use vandles\service\UserInfoService;

class UserInfoTokenMiddleware extends BaseMiddleware{

    /**
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next) {

        // 是否强制token
        $isForce = $request->rule()->getOption('isForce', true);
        // 开发测试用
        if(input("is_dev")){
            $userInfo = UserInfoService::instance()->getUserInfoByOpenid("obzAZ7c85NvxGhEXyOEXnP6uY0JM");
        }else{
            $token = $request->header('token');
            if($isForce && !$token) $this->error("token不存在", [], 401);
            if($token) {
                $userInfo = UserInfoService::instance()->parseJWTToken($token);
                if(!$userInfo){
                    $userInfo = cache($token);
                }
                if($isForce && !$userInfo) $this->error("token已过期", [], 401);
            }else $userInfo = [];
        }
        // $userInfo = UserInfoService::instance()->getUserInfoByOpenid($userInfo['openid'], "id,openid");
        // if(!$userInfo) $this->error("用户不存在", [], 401);

        $request::macro('uid', function() use($userInfo){
            return $userInfo['id']??0;
        });
        $request::macro('openid', function() use($userInfo){
            return $userInfo['openid']??null;
        });
        return $next($request);
    }

}