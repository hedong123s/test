<?php
namespace Admin\Controller;

use Think\PageAdmin;
use Common\Support\Validator;

/**
 * 内容管理相关
 * 
 * @author Flc <2016-08-23 19:29:09>
 */
class ArticleController extends BaseController
{
    /**
     * 文章列表
     * @return [type] [description]
     */
    public function index()
    {
        $prefix = C('DB_PREFIX');

        $count = M('articles')->count();
        $page  = new PageAdmin($count, C('PAGE_NUM'));
        
        $pages['show']  = $page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = M('articles a')
            ->field('a.*, ac.name category_name')
            ->join($prefix . 'article_category ac on ac.id = a.id', 'left')
            ->order("a.sortid asc, a.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();

        $this->seotitle = '文档列表';
        $this->pages    = $pages;
        $this->display();
    }

    /**
     * 文档添加
     * @return [type] [description]
     */
    public function add()
    {
        $maxSortId = M('articles')->max('sortid');
        $categorys = M('article_category')->field('id, name')->order(['sortid' => 'asc', 'id' => 'asc'])->select();

        $this->seotitle  = '文档添加';
        $this->maxSortId = $maxSortId + 1;
        $this->categorys = $categorys;
        $this->display();
    }

    /**
     * 文档编辑
     * @return [type] [description]
     */
    public function edit()
    {
        $id = I('get.id', 0, 'intval');

        if (empty($id) ||
            ! $info = M('articles')->where(['id' => $id])->find())
        {
            $this->error('参数错误');
        }

        $categorys = M('article_category')->field('id, name')->order(['sortid' => 'asc', 'id' => 'asc'])->select();

        $this->seotitle  = '文档编辑';
        $this->info      = $info;
        $this->categorys = $categorys;
        $this->display();
    }

    /**
     * 文章保存
     * @return [type] [description]
     */
    public function save()
    {
        $action = I('get.action', '', 'trim');
        $params = I('post.');

        // 校验规则
        $rules = [
            'title'  => 'required',
            'cid'    => 'required|integer|min:1',
            'sortid' => 'required|integer'
        ];

        $messages = [
            'title.required'  => '请输入文章标题',
            'cid.required'    => '请选择分类',
            'cid.integer'     => '分类参数错误',
            'cid.min'         => '分类参数错误',
            'sortid.required' => '请输入排序编号',
            'sortid.integer'  => '排序编号必须为整数'
        ];

        // 新增
        if ($action == 'add') {
            $v = Validator::make($params, $rules, $messages);

            if ($v->fails()) {
                $this->error($v->errors()->first());
            }

            M('articles')->add([
                'title'       => trim($params['title']),
                'cid'         => $params['cid'],
                'description' => $params['description'],
                'keyword'     => $params['keyword'],
                'status'      => $params['status'] == 1 ? 1 : 0,
                'sortid'      => intval($params['sortid']),
                'content'     => $params['content'],
                'thumb'       => intval($params['thumb']),
                'created_at'  => time(),
                'updated_at'  => time()
            ]);

            $this->success('操作成功', U('index'));
        }

        // 编辑保存
        elseif ($action == 'edit') {
            $id = I('post.id', 0, 'intval');            

            $v = Validator::make($params, $rules, $messages);

            if ($v->fails()) {
                $this->error($v->errors()->first());
            }

            M('articles')->where(['id' => $id])->save([
                'title'       => trim($params['title']),
                'cid'         => $params['cid'],
                'description' => $params['description'],
                'keyword'     => $params['keyword'],
                'status'      => $params['status'] == 1 ? 1 : 0,
                'sortid'      => intval($params['sortid']),
                'content'     => $params['content'],
                'thumb'       => intval($params['thumb']),
                'created_at'  => time()
            ]);

            $this->success('操作成功', U('index'));
        }
    }

    /**
     * 文章删除
     * @return [type] [description]
     */
    public function delete()
    {
        $id = I('get.id', 0, 'intval');

        M('articles')->where(['id' => $id])->delete();

        $this->success('操作成功');
    }

    /**
     * 分类列表
     * @return [type] [description]
     */
    public function category()
    {
        $count = M('article_category')->count();
        $page  = new PageAdmin($count, C('PAGE_NUM'));
        
        $pages['show']  = $page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = M('article_category')->order("sortid asc, id asc")->limit($page->firstRow.','.$page->listRows)->select();

        $this->seotitle = '文档分类';
        $this->pages    = $pages;
        $this->display();
    }

    /**
     * 分类添加
     * @return [type] [description]
     */
    public function category_add()
    {
        $maxSortId = M('article_category')->max('sortid');

        $this->seotitle  = '文档分类添加';
        $this->maxSortId = $maxSortId + 1;
        $this->display();
    }

    /**
     * 分类编辑
     * @return [type] [description]
     */
    public function category_edit()
    {
        $id = I('get.id', 0, 'intval');

        if (empty($id) ||
            ! $info = M('article_category')->where(['id' => $id])->find())
        {
            $this->error('参数错误');
        }

        $this->seotitle = '文档分类编辑';
        $this->info     = $info;
        $this->display();
    }

    /**
     * 分类保存
     * @return [type] [description]
     */
    public function category_save()
    {
        $action = I('get.action', '', 'trim');
        $params = I('post.');

        // 校验规则
        $rules = [
            'name'   => 'required',
            'sortid' => 'required|integer'
        ];

        $messages = [
            'name.required'   => '请输入分类名称',
            'sortid.required' => '请输入排序编号',
            'sortid.integer'  => '排序编号必须为整数'
        ];

        // 新增
        if ($action == 'add') {
            $v = Validator::make($params, $rules, $messages);

            if ($v->fails()) {
                $this->error($v->errors()->first());
            }

            M('article_category')->add([
                'name'        => trim($params['name']),
                'description' => $params['description'],
                'keyword'     => $params['keyword'],
                'status'      => $params['status'] == 1 ? 1 : 0,
                'sortid'      => intval($params['sortid']),
                'created_at'  => time(),
                'updated_at'  => time()
            ]);

            $this->success('操作成功', U('category'));
        }

        // 编辑保存
        elseif ($action == 'edit') {
            $id = I('post.id', 0, 'intval');            

            $v = Validator::make($params, $rules, $messages);

            if ($v->fails()) {
                $this->error($v->errors()->first());
            }

            M('article_category')->where(['id' => $id])->save([
                'name'        => trim($params['name']),
                'description' => $params['description'],
                'keyword'     => $params['keyword'],
                'status'      => $params['status'] == 1 ? 1 : 0,
                'sortid'      => intval($params['sortid']),
                'updated_at'  => time()
            ]);

            $this->success('操作成功', U('category'));
        }
    }

    /**
     * 分类删除
     * @return [type] [description]
     */
    public function category_delete()
    {
        $id = I('get.id', 0, 'intval');

        if (M('articles')->where(['cid' => $id])->count() > 0) {
            $this->error('当前分类有文章，请先删除文章');
        }

        M('article_category')->where(['id' => $id])->delete();

        $this->success('操作成功');
    }
}
?>