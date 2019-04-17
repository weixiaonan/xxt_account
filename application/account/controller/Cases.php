<?php

// +----------------------------------------------------------------------
// | framework
// +----------------------------------------------------------------------
// | 版权所有 2014~2018 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://framework.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\account\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 案件管理
 * Class Cases
 * @package app\store\controller
 */
class Cases extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'cases';

    /**
     * 案件列表
    */
    public function index()
    {
        $this->title = '案件管理';
        return $this->_query($this->table)
            ->equal('status')
            ->like('title')
            ->where(['is_deleted' => '0'])
            ->order('id desc')
            ->page();
    }

    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data)
    {
        foreach ($data as &$row) {
            $row['case_img_arr'] = explode('|', $row['case_img']);
            $row['events_num']   = DB::name('events')->where(['cases_id' => $row['id']])->count();
        }
    }

    /**
     * 录入案件
     * @return mixed
     */
    public function add()
    {
        $this->title = '录入案件';
        $this->isAddMode = '1';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑商品信息
     * @return mixed
     */
    public function edit()
    {
        $this->title = '编辑案件';
        $this->isAddMode = '0';
        return $this->_form($this->table, 'form');
    }

    /**
     * 表单数据处理
     * @param array $data
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    protected function _form_filter(&$data)
    {
        // 添加
        if ($this->request->isGet()) {
            if ($data) {
                if ($data['start_time'] > 0) {
                    $data['start_time'] = date('Y-m-d', $data['start_time']);
                } else {
                    $data['start_time'] = '';
                }
                if ($data['end_time'] > 0) {
                    $data['end_time'] = date('Y-m-d', $data['end_time']);
                } else {
                    $data['end_time'] = '';
                }
            }

        } elseif ($this->request->isPost()) {
            if (!isset($data['id'])) {
                $data['add_time'] = time();
                $data['add_uid']  = session('user.id');
            }
            if (isset($data['start_time'])) {
                $data['start_time'] = strtotime($data['start_time']);
            }
            if (isset($data['end_time'])) {
                $data['end_time']   = strtotime($data['end_time']);
            }
        }
    }

    /**
     * 表单结果处理
     * @param boolean $result
     */
    protected function _form_result($result)
    {
        if ($result && $this->request->isPost()) {
            $this->success('保存案件成功！', 'javascript:history.back()');
        }
    }

    /**
     * 结案
     */
    public function close_case()
    {
        $this->_save($this->table, ['status' => '1', 'end_time' => time()]);
    }



    /**
     * 删除
     */
    public function del()
    {
        $this->_delete($this->table);
    }

    /**
     * 单个案件时间轴
     */
    public function showTimeAxis()
    {
        $icon_arr = [
            'triangle_black.png',
            'triangle_blue.png',
            'triangle_green.png',
            'triangle_orange.png',
            'triangle_purple.png',
            'triangle_red.png',
            'triangle_yellow.png',
            'circle_blue.png',
            'circle_purple.png',
        ];

        $case_id = $this->request->get('id', 0, 'intval');
        $icon_index = 0;
        if ($case_id) {
            $case   = Db::name($this->table)->find($case_id);
            $events = Db::name('events')->where(['is_deleted' => 0])->where(['cases_id' => $case_id])->select();
            $events_data = [];
            $legend_data = [];
            foreach ($events as $k=>$e) {
                $row    = [];
                $legend = [];
                   $row = array(
                        'id'          =>  "0" . $e['id'],
                        'title'       => $e['title'],
                        'description' => $e['content'],
                        'modal_type'  => 'full',
                        'high_threshold' => 50,
                        'importance'  => 18, //事件div高度
                        'span_color'  => '#0684f0',
                        'startdate'   => date('Y-m-d H:i:s', $e['start_time']),
                        'enddate'     => date('Y-m-d H:i:s', $e['end_time']),
                        'icon'        => $icon_arr[$icon_index],
                       'images'        =>  explode('|', $e['scan_img'])

                    );
                $images = explode('|', $e['scan_img']);
                if (count($images) > 0) $row['image'] = $images[0];
                $legend = array('title' => $e['title'], 'icon' => $icon_arr[$k]);
                $events_data[] = $row;
                $legend_data[] = $legend;

                $icon_index++;
                if ($icon_index >= count($icon_arr)) $icon_index = 0;
            }
            $data = array(
                'id'          => 'case_' . $case_id,
                'title'       => $case['title'],
                'description' => $case['content'],
                'focus_date'  => date('Y-m-d', $case['start_time']),
                'initial_zoom'  => "16",
                'image_lane_height'  => 50,
                'events'      => $events_data,
                'tags'        => array('mardigras' => 2, 'chris' => 2, 'arizona' => 2, 'netscape' => 2, 'flop' => 2),
                'legend'      => $legend_data
            );

            $this->assign('json_data', json_encode([$data], JSON_UNESCAPED_UNICODE));
            return $this->fetch();
        } else {
            $this->error('未知参数错误！');
        }

    }

    /**
     * 全部案件时间轴
     */
    public function showAllTimeAxis()
    {
        return $this->fetch();
    }

    /**
     * 全部案件时间轴
     */
    public function showAllTimeData()
    {
        $icon_arr = [
            'triangle_black.png',
            'triangle_blue.png',
            'triangle_green.png',
            'triangle_orange.png',
            'triangle_purple.png',
            'triangle_red.png',
            'triangle_yellow.png',
            'circle_blue.png',
            'circle_purple.png',
        ];

        $case_id = 1;
        $icon_index = 0;
        if ($case_id) {
            $case   = array('title' => '全部案件', 'content' => '', 'start_time' => time());
            $cases= Db::name('cases')->where(['is_deleted' => 0])->select();
            $events_data = [];
            $legend_data = [];
            foreach ($cases as $k=>$e) {
                $row    = [];
                $legend = [];
                $row = array(
                    'id'          =>  "0" . $e['id'],
                    'title'       => $e['title'],
                    'description' => $e['content'],
                    'modal_type'  => 'full',
                    'high_threshold' => 50,
                    'importance'  => 18, //事件div高度
                    'span_color'  => '#0684f0',
                    'startdate'   => date('Y-m-d H:i:s', $e['start_time']),
                    'enddate'     => date('Y-m-d H:i:s', $e['end_time']),
                    'icon'        => $icon_arr[$icon_index],
                    'images'        =>  explode('|', $e['case_img'])

                );
                $images = explode('|', $e['case_img']);
                if (count($images) > 0) $row['image'] = $images[0];
                $legend = array('title' => $e['title'], 'icon' => $icon_arr[$k]);
                $events_data[] = $row;
                $legend_data[] = $legend;

                $icon_index++;
                if ($icon_index >= count($icon_arr)) $icon_index = 0;

            }
            $data = array(
                'id'          => 'case_' . $case_id,
                'title'       => $case['title'],
                'description' => $case['content'],
                'focus_date'  => date('Y-m-d', $case['start_time']),
                'initial_zoom'  => "16",
                'image_lane_height'  => 50,
                'events'      => $events_data,
                'tags'        => array('mardigras' => 2, 'chris' => 2, 'arizona' => 2, 'netscape' => 2, 'flop' => 2),
                'legend'      => $legend_data
            );

            $this->assign('json_data', json_encode([$data], JSON_UNESCAPED_UNICODE));
            return $this->fetch('show_time_axis');
        } else {
            $this->error('未知参数错误！');
        }

    }



    /*
     * 统计管理
     */
    public function showECharts()
    {
        $this->title = '数据统计';
        $data   = [];
        $months = [];
        $events_num = [];
        $cases_num  = [];
        for($i = 11; $i>-1; $i--) {
            $months[]     = date('Y-m', strtotime(last_month(time(),$i)));
            $month_start_end = get_the_month(date('Y-m', strtotime(last_month(time(),$i))));

            $cases_num[]  = Db::name('cases')->where(['is_deleted' => 0])->whereTime('start_time', $month_start_end)->count();
            $events_num[] = Db::name('events')->where(['is_deleted' => 0])->whereTime('start_time', $month_start_end)->count();
        }

        $week_start_time  = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
        $week_end_time    = date('Y-m-d  23:59:59', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));

        $month_start_time = date('Y-m-01', time());
        $month_end_time   = date('Y-m-d 23:59:59', strtotime("$month_start_time +1 month -1 day"));

        //全部案件
        $data['cases_all_num']   = Db::name('cases')->where(['is_deleted' => 0])->count();
        //本周案件
        $data['cases_week_num']  = Db::name('cases')->where(['is_deleted' => 0])->whereTime('start_time', [$week_start_time, $week_end_time])->count();
        //本月案件
        $data['cases_month_num'] = Db::name('cases')->where(['is_deleted' => 0])->whereTime('start_time', [$month_start_time, $month_end_time])->count();

        //全部案件
        $data['events_all_num']  = Db::name('events')->where(['is_deleted' => 0])->count();
        //本周事件
        $data['events_week_num'] = Db::name('events')->where(['is_deleted' => 0])->whereTime('start_time', [$week_start_time, $week_end_time])->count();
        //本月案件
        $data['events_month_num']= Db::name('events')->where(['is_deleted' => 0])->whereTime('start_time', [$month_start_time, $month_end_time])->count();

        $this->assign($data);
        $this->assign('months_text', json_encode($months));
        $this->assign('events_num', json_encode($events_num));
        $this->assign('cases_num', json_encode($cases_num));
        return $this->fetch();
    }

}