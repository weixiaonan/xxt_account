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
     * 案件时间轴
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
        if ($case_id) {
            $case   = Db::name($this->table)->find($case_id);
            $events = Db::name('events')->where(['cases_id' => $case_id])->select();
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
                        'icon'        => $icon_arr[$k]
                    );

                $legend = array('title' => $e['title'], 'icon' => $icon_arr[$k]);
                $events_data[] = $row;
                $legend_data[] = $legend;


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

    /*
     * 统计管理
     */
    public function showECharts()
    {
        $this->title = '数据统计';
        $this->assign('data', []);
        return $this->fetch();
    }

}