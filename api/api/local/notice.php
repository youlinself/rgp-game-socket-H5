<?php
/**
 * User: xiaoqing Email: liuxiaoqing437@gmail.com
 * Date: 2017/7/17
 * Time: 下午2:54
 * 通知接口
 */
//$p = API::param()->getParams();
//if (md5($p['time'].SERVER_KEY) != $p['sign']) API::out('fail', 'sign error');
$time = time();

//获取结果
$list = Db::getInstance()->getAll("SELECT type,title,content FROM sys_notice_board WHERE type IN (1, 2) AND start_time<={$time} AND end_time>={$time} ORDER BY sort ASC ");

if (empty($list)) {
    API::outlua('fail', '无符合条件通知');
} else {
    API::outlua('success', '获取成功', $list);
}

