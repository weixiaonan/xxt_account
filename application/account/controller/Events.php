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
use function Qiniu\json_decode;
use think\Db;

/**
 * 事件管理
 * Class Cases
 * @package app\store\controller
 */
class Events extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'events';

    /**
     * 事件列表
    */
    public function index()
    {
        $this->title = '事件管理';
        $case_id     = $this->request->get('cases_id', 0);
        $where       = [];
        $where[]     = ['is_deleted', '=', '0'];
        if ($case_id) $where[]     = ['cases_id', '=', $case_id];
        return $this->_query($this->table)
                    ->equal('status')
                    ->like('title')
                    ->where($where)
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
            $row['scan_img_arr'] = explode('|', $row['scan_img']);
            $row['case_title']   = DB::name('cases')->where(['id' => $row['cases_id']])->value('title');
         }

    }



    /**
     * 录入案件
     * @return mixed
     */
    public function add()
    {
        $this->title = '录入事件';
        $this->isAddMode = '1';
        $cases_list = DB::name('cases')->where('is_deleted', '=', 0)->field('id,title')->select();
        $this->assign('cases_list', $cases_list);
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑商品信息
     * @return mixed
     */
    public function edit()
    {
        $this->title = '编辑事件';
        $this->isAddMode = '0';
        $cases_list = DB::name('cases')->where('is_deleted', '=', 0)->field('id,title')->select();
        $this->assign('cases_list', $cases_list);
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
            if ($data['cases_id'] == '') {
                $this->error('请选择该事件所属案件！');
            }
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
            $this->success('保存事件成功！', 'javascript:history.back()');
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
     * 删除商品
     */
    public function del()
    {
        $this->_delete($this->table);
    }

    function _sendOCR()
    {

        require_once './baidu_ocr/AipOcr.php';
        // 你的 百度APPID AK SK
        $APP_ID = '15984790';
        $API_KEY = 'pGeyGNXvZI4DYR0MBakXG4t5';
        $SECRET_KEY = 'taDRNG17SgriApUDlghPTsj7KPRhGXEX';

        $client = new \AipOcr($APP_ID, $API_KEY, $SECRET_KEY);

        $img_url = $this->request->post('img_url', '');
        //$image = file_get_contents('http://obj.qinlemon.com/car_manage/timg1.jpg');
        $image = @file_get_contents($img_url);

        // 调用通用文字识别, 图片参数为本地图片
        $client->basicGeneral($image);

        // 如果有可选参数
        $options = array();
        $options["language_type"] = "CHN_ENG";
        $options["detect_direction"] = "true";
        $options["detect_language"] = "true";
        $options["probability"] = "true";

        // 带参数调用通用文字识别, 图片参数为本地图片
        $res = $client->basicGeneral($image, $options);

        if (isset($res['words_result']) && count($res['words_result']) > 0) {
            echo json_encode(['code' => 0, 'msg' => $res['words_result'][0]['words']]);
        } else if (isset($res['error_code'])){
            echo json_encode(['code' => 1, 'msg' => $res['error_code']]);
        } else {
            echo json_encode(['code' => 0, 'msg' => '']);
        }
        exit;

    }



}