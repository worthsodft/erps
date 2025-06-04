<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 16:02
 * Email: <vandles@qq.com>
 **/

namespace vandles\model;


use think\admin\extend\DataExtend;
use think\facade\Db;
use vandles\lib\VException;

class GoodsCateModel extends BaseSoftDeleteModel {
    protected $name = 'AGoodsCate';

    /**
     * 获取上级可用选项
     * @param int $max 最大级别
     * @param array $data 表单数据
     * @param array $parent 上级分类
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getParentData(int $max, array &$data, array $parent = []): array {
        $items = static::mk()->where(['deleted' => 0])->order('sort desc,id asc')->select()->toArray();
        $cates = DataExtend::arr2table(empty($parent) ? $items : array_merge([$parent], $items));
        if (isset($data['id'])) foreach ($cates as $cate) if ($cate['id'] === $data['id']) $data = $cate;
        foreach ($cates as $key => $cate) {
            $isSelf = isset($data['spt']) && isset($data['spc']) && $data['spt'] <= $cate['spt'] && $data['spc'] > 0;
            if ($cate['spt'] >= $max || $isSelf) unset($cates[$key]);
        }
        return $cates;
    }

    /**
     * 获取数据树
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function treeData(): array {
        $query = static::mk()->where(['status' => 1, 'deleted' => 0])->order('sort desc,id desc');
        return DataExtend::arr2tree($query->withoutField('sort,status,deleted,create_at')->select()->toArray());
    }

    /**
     * 获取列表数据
     * @param bool $simple 仅子级别
     * @return array
     */
    public static function treeTable(bool $simple = false, $goods_type=-1): array {
        $query = static::mk()->where(['status' => 1, 'deleted' => 0])->order('sort desc,id asc');
        // if($goods_type == 0) $query->where(['goods_type' => 0]);
        // elseif($goods_type == 1) $query->where(['goods_type' => 1]);
        if($goods_type != -1) $query->where(['goods_type' => $goods_type]);
        $cates = array_column(DataExtend::arr2table($query->column('id,pid,title', 'id')), null, 'id');
        foreach ($cates as $cate) isset($cates[$cate['pid']]) && $cates[$cate['id']]['parent'] =& $cates[$cate['pid']];
        foreach ($cates as $key => $cate) {
            $id = $cate['id'];
            $cates[$id]['ids'][] = $cate['id'];
            $cates[$id]['titles'][] = $cate['title'];
            while (isset($cate['parent']) && ($cate = $cate['parent'])) {
                $cates[$id]['ids'][] = $cate['id'];
                $cates[$id]['titles'][] = $cate['title'];
            }
            $cates[$id]['ids'] = array_reverse($cates[$id]['ids']);
            $cates[$id]['titles'] = array_reverse($cates[$id]['titles']);
            if (isset($pky) && $simple && in_array($cates[$pky]['title'], $cates[$id]['titles'])) {
                unset($cates[$pky]);
            }
            $pky = $key;
        }
        foreach ($cates as &$cate) unset($cate['parent']);
        return array_values($cates);
    }
}