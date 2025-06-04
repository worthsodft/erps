<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2025/1/16
 * Time: 16:14
 */

namespace vandles\lib\code;

use think\facade\Db;
use think\helper\Str;
use vandles\lib\VException;

/**
 * 代码生成器
 * 根据表备注，生成代码
 * 表备注：{字段标题}|{字段描述}:0=>已隐藏,1=>已显示
 * 示例：
 * 1. 卡号
 * 2. 余额|类型为金额时有效
 * 3. 使用类型:金额,次数
 * 4. 状态:0=已隐藏,1=已显示
 *
 * 作用方法：
 * CodeBuilder::instance()->setControlPrefix("sale")->buildAll("gift_card", "实物卡管理");
 *
 * 会创建三个类：
 * extend\vandles\model\GiftCardModel.php
 * extend\vandles\service\GiftCardService.php
 * app\master\controller\sale\Giftcard.php
 *
 * 三个页面文件：
 * app\master\view\sale\giftcard\index.html
 * app\master\view\sale\giftcard\index_search.html
 * master\view\sale\giftcard\form.html
 *
 */
class CodeBuilder {
    private static $obj;
    private $appName = 'master';
    private $tablePrefix = 'a';
    private $controlPrefix = '';
    private $isForce = false; // 是否强制生成，即：存在也生成
    private $isAddon = false; // 是否是创建插件
    private $isAddonMaster = false; // 是否是创建插件后台
    private $typeNumbers = ['float' => 1, 'int' => 1];
    private $typeDates = ['date' => 1, 'datetime' => 1, 'timestamp' => 1];

    public static function instance(): CodeBuilder {
        if (!self::$obj) self::$obj = new self();
        return static::$obj;
    }

    public function setControlPrefix(string $controlPrefix) {
        $this->controlPrefix = $controlPrefix;
        return $this;
    }

    public function getControlPrefix() {
        return $this->controlPrefix;
    }

    public function setIsForce(bool $isForce) {
        $this->isForce = $isForce;
        return $this;
    }

    public function getIsForce() {
        return $this->isForce;
    }

    public function setIsAddon(bool $isAddon=false) {
        $this->isAddon = $isAddon;
        return $this;
    }

    public function getIsAddon() {
        return $this->isAddon;
    }

    public function setIsAddonMaster(bool $isAddonMaster=false) {
        $this->isAddonMaster = $isAddonMaster;
        return $this;
    }

    public function getIsAddonMaster() {
        return $this->isAddonMaster;
    }

    /**
     * 生成所有
     * @param string $tableName
     * @return bool
     */
    public function buildAll(string $tableName, string $title) {

        // 判断数据库是否已连接
        if (!Db::connect()->getPdo()) VException::throw("数据库未连接");

        // 生成model
        $fileModel = $this->buildModel($tableName);
        // dd("生成model");

        // 生成service
        $fileService = $this->buildService($tableName);
        // dd("生成service");

        // 生成controller
        $fileController = $this->buildController($tableName, $title);
        // dd("生成controller");

        // 生成view
        $fileViews = $this->buildViews($tableName);
        // dd("生成view");

        // d($fileModel);
        // d($fileService);
        // d($fileController);
        // dd($fileViews);
        return true;
    }

    /**
     * 生成列表页面
     * @param string $tableName
     * @return array
     * @throws \Exception
     */
    public function buildViewIndex(string $tableName): array {
        $fileName = str_replace('_', '', $tableName);

        if ($this->isAddonMaster) {
            if (empty($this->controlPrefix)) throw new \Exception("插件名称不能为空");
            $filePath      = app()->getRootPath() . "app\addon{$this->controlPrefix}\\view\\master";
            $controlPrefix = $this->controlPrefix . '/';
        } else {
            $filePath = app()->getRootPath() . 'app\master\view';
            if ($this->controlPrefix) {
                $filePath      .= DIRECTORY_SEPARATOR . $this->controlPrefix;
                $controlPrefix = $this->controlPrefix . '/';
            } else $controlPrefix = $this->controlPrefix;
        }

        $filePath = $filePath . DIRECTORY_SEPARATOR . $fileName . DIRECTORY_SEPARATOR;
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }
        $fileNameIndex       = $filePath . 'index.html';
        $fileNameIndexSearch = $filePath . 'index_search.html';

        $tplIndex = __DIR__ . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'index.html';
        if (!file_exists($tplIndex)) VException::throw("index模板文件不存在");
        $tplSearch = __DIR__ . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'index_search.html';
        if (!file_exists($tplSearch)) VException::throw("index_search模板文件不存在");

        $columns = $this->getColumns($tableName);
        unset($columns['deleted_time']); // 此项不显示

        $columns1 = $columns2 = $columns; // 后面要用

        // 写入index.html文件
        if (file_exists($fileNameIndex) && !$this->isForce) $fileNameIndex = "保持：" . $fileNameIndex;
        else {
            $tplIndexContent = file_get_contents($tplIndex);
            // 去掉这4个，因为这4个字段是必须要有的，在模板中已固化
            unset($columns1['sort']);
            unset($columns1['id']);
            unset($columns1['status']);
            unset($columns1['create_at']);
            $tableData = [];
            foreach ($columns1 as $v) {
                if (!empty($v['opts'])) $tableData[] = $this->getIndexItemSelect($v);
                elseif($v['type'] == 'pic') $tableData[] = $this->getIndexItemPic($v);
                else $tableData[] = $this->getIndexItemText($v);
            }
            $tableData = implode("\n", $tableData);


            $searchs  = ['{{FileName}}', '{{ControllerPrefix}}', '{{TableData}}'];
            $replaces = [$fileName, $controlPrefix, $tableData];

            if ($this->isAddonMaster) {
                $searchs[]  = "{{SearchIndexPrefix}}";
                $replaces[] = "master/{$fileName}";
            } else {
                $searchs[]  = "{{SearchIndexPrefix}}";
                $replaces[] = $controlPrefix . $fileName;
            }
            $tplIndexContent = str_replace($searchs, $replaces, $tplIndexContent);

            file_put_contents($fileNameIndex, $tplIndexContent);
            $fileNameIndex = "新建：" . $fileNameIndex;
            // 写入index.html文件 结束
        }

        // 写入index_search.html文件
        if (file_exists($fileNameIndexSearch) && !$this->isForce) $fileNameIndexSearch = "保持：" . $fileNameIndexSearch;
        else {
            $tplSearchContent = file_get_contents($tplSearch);
            unset($columns1['status']); // 此项不显示
            $searchData = [];
            foreach ($columns2 as $field => $v) {
                if (!empty($v['opts'])) $searchData[] = $this->getIndexSearchItemSelect($v);
                elseif ($v['type'] == 'datetime') $searchData[] = $this->getIndexSearchItemDateRange($field, $v['title']);
                elseif (!in_array($v['type'], ['pic','images'])) $searchData[] = $this->getIndexSearchItemText($field, $v['title']);
            }
            $searchData = implode("\n", $searchData);

            $exportDataTitle = $exportDataWidth = $exportDataValue = [];
            $i               = 0;

            foreach ($columns as $column) {
                $exportDataTitle[] = "'{$column['title']}'";
                $exportDataWidth[] = $column['field'] == 'id' ? 60 : 120;
                $exportDataValue[] = ($i == 0 ? "" : "                    ") . "item.{$column['field']},";
                $i++;
            }
            $exportDataTitle = implode(',', $exportDataTitle);
            $exportDataWidth = implode(',', $exportDataWidth);
            $exportDataValue = implode("\n", $exportDataValue);

            $tplSearchContent = str_replace(['{{SearchData}}', '{{ExportDataTitle}}', '{{ExportDataWidth}}', '{{ExportDataValue}}'], [$searchData, $exportDataTitle, $exportDataWidth, $exportDataValue], $tplSearchContent);
            file_put_contents($fileNameIndexSearch, $tplSearchContent);
            $fileNameIndexSearch = "新建：" . $fileNameIndexSearch;
            // 写入index_search.html文件 结束
        }

        return ['index' => $fileNameIndex, 'search' => $fileNameIndexSearch];
    }

    /**
     * 生成表单页面
     * @param string $tableName
     * @return string
     */
    public function buildViewForm(string $tableName): string {
        $fileName = str_replace('_', '', $tableName);

        if ($this->isAddonMaster) {
            if (empty($this->controlPrefix)) throw new \Exception("插件名称不能为空");
            $filePath = app()->getRootPath() . "app\addon{$this->controlPrefix}\\view\\master";

        } else {
            $filePath = app()->getRootPath() . 'app\master\view';
            if ($this->controlPrefix) {
                $filePath .= DIRECTORY_SEPARATOR . $this->controlPrefix;
            }
        }

        $filePath = $filePath . DIRECTORY_SEPARATOR . $fileName . DIRECTORY_SEPARATOR;
        $fileName = $filePath . 'form.html';

        // 写入form.html文件
        if (file_exists($fileName) && !$this->isForce) return "保持：" . $fileName;

        $tplFile = __DIR__ . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'form.html';
        if (!file_exists($tplFile)) VException::throw("form模板文件不存在");

        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }


        $columns = $this->getColumns($tableName);
        unset($columns['deleted_time']); // 此项不显示
        unset($columns['id']); // 此项不显示
        unset($columns['status']); // 此项不显示
        unset($columns['sort']); // 此项不显示
        unset($columns['create_at']); // 此项不显示
        unset($columns['update_at']); // 此项不显示
        $tplContent = file_get_contents($tplFile);

        $formData = [];
        foreach ($columns as $v) {
            if (!empty($v['opts'])) $formData[] = $this->getFormItemSelect($v);
            elseif ($v['type'] == 'datetime') $formData[] = $this->getFormItemDatetime($v);
            elseif ($v['type'] == 'number') $formData[] = $this->getFormItemNumber($v);
            elseif ($v['type'] == 'pic') $formData[] = $this->getFormItemPic($v);
            else $formData[] = $this->getFormItemText($v);
        }
        // dd($formData);
        $formData   = implode("\n", $formData);
        $tplContent = str_replace(['{{FormData}}'], [$formData], $tplContent);
        file_put_contents($fileName, $tplContent);
        // 写入form.html文件 结束

        return "新建：" . $fileName;
    }

    /**
     * 生成视图（共3个文件）
     * @param string $tableName
     * @return array
     */
    public function buildViews(string $tableName) {
        ['index' => $fileNameIndex, 'search' => $fileNameIndexSearch] = $this->buildViewIndex($tableName);
        $fileNameForm = $this->buildViewForm($tableName);;
        return ['index' => $fileNameIndex, 'search' => $fileNameIndexSearch, 'form' => $fileNameForm];
    }

    /**
     * 生成controller
     * @param string $tableName
     * @param string $title
     * @return string
     */
    public function buildController(string $tableName, string $title) {
        $name       = "controller";
        $table_name = $tableName;
        $tableName  = $this->pascal2big($tableName);

        if ($this->isAddonMaster) {
            if (empty($this->controlPrefix)) throw new \Exception("插件名称不能为空");
            $namespace = "app\addon{$this->controlPrefix}\controller\master";
        } else {
            $namespace = "app\master\controller";
            if ($this->controlPrefix) $namespace .= "\\" . $this->controlPrefix;
        }

        $className = ucfirst(strtolower($tableName));

        $root     = app()->getRootPath();
        $filePath = $root . $namespace . DIRECTORY_SEPARATOR;
        $fileName = $filePath . $className . ".php";
        if (file_exists($fileName) && !$this->isForce) return "保持：" . $fileName;

        $tplFile = __DIR__ . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . "{$name}.tpl";
        if (!file_exists($tplFile)) VException::throw("{$name}模板文件不存在");

        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $columns = $this->getColumns($table_name);
        unset($columns['deleted_time']); // 此项不显示
        unset($columns['sort']); // 此项不显示

        $likeStr = $equalsStr = $dateStr = [];
        foreach ($columns as $field => $v) {
            if (!empty($v['opts'])) $equalsStr[] = $field;
            elseif (!empty($this->typeDates[$v['type']])) $dateStr[] = $field;
            else $likeStr[] = $field;
        }
        $likeStr   = implode(",", $likeStr);
        $equalsStr = implode(",", $equalsStr);
        $dateStr   = implode(",", $dateStr);

        $controlData[] = "           #query->like('$likeStr');";
        $controlData[] = "           #query->equal('$equalsStr');";
        $controlData[] = "           #query->dateBetween('$dateStr');";

        $controlData = implode("\n", $controlData);
        $controlData = str_replace("#", "$", $controlData);
        $tplContent  = file_get_contents($tplFile);

        $searchs  = ['{{Namespace}}', '{{ClassName}}', '{{TableName}}', '{{Title}}', '{{ControlData}}'];
        $replaces = [$namespace, $className, $tableName, $title, $controlData];

        if ($this->isAddonMaster) {
            $searchs[]  = "{{UseService}}";
            $replaces[] = "app\\addon{$this->controlPrefix}\service";
        } else {
            $searchs[]  = "{{UseService}}";
            $replaces[] = "vandles\service";
        }
        $tplContent = str_replace($searchs, $replaces, $tplContent);

        file_put_contents($fileName, $tplContent);

        return "新建：" . $fileName;
    }

    /**
     * 生成model
     * @param string $tableName
     * @return string
     */
    public function buildModel(string $tableName) {
        $name = "model";
        ['fileName' => $fileName, 'tplContent' => $tplContent] = $this->buildToExtend($tableName, $name);
        if ($tplContent == 'is_exist') return "保持：" . $fileName;

        // model类需要渲染一些内容
        $columns = $this->getColumns($tableName);
        unset($columns['deleted_time']); // 此项不显示
        unset($columns['sort']); // 此项不显示
        // unset($columns['status']); // 此项不显示

        $optsData = [];
        foreach ($columns as $field => $v) {
            if (!empty($v['opts'])) {
                $optsData[] = $this->getModelOptsData($v);
            }
        }
        $optsData   = implode("\n", $optsData);
        $tplContent = str_replace(['{{OptsData}}'], [$optsData], $tplContent);

        file_put_contents($fileName, $tplContent);
        return "新建：" . $fileName;

    }

    private function getModelOptsData($v): string {
        $optsName = $this->pascal2big($v['field']);
        $opts     = var_export($v['opts'], true);
        $opts     = str_replace("\n", '', $opts);
        $content  = <<<EOT
    public static function get{$optsName}s() {
        return {$opts};
    }
        
EOT;
        return $content;
    }

    /**
     * 生成service
     * @param string $tableName
     * @return string
     */
    public function buildService(string $tableName) {
        $name = "service";
        ['fileName' => $fileName, 'tplContent' => $tplContent] = $this->buildToExtend($tableName, $name);
        if ($tplContent == 'is_exist') return "保持：" . $fileName;

        // service类需要渲染一些内容
        $columns = $this->getColumns($tableName);
        unset($columns['deleted_time']); // 此项不显示
        unset($columns['sort']); // 此项不显示
        // unset($columns['status']); // 此项不显示

        $bindDataOpts = $bindDataTxt = [];
        $i            = 0;
        foreach ($columns as $field => $v) {
            if (!empty($v['opts'])) {
                $data           = $this->getServiceBindData($v, $tableName);
                $bindDataOpts[] = ($i == 0 ? "" : "        ") . $data[0];
                $bindDataTxt[]  = ($i == 0 ? "" : "            ") . $data[1];
                $i++;
            }
        }

        $bindDataOpts = implode("\n", $bindDataOpts);
        $bindDataTxt  = implode("\n", $bindDataTxt);

        $searchs  = ['{{BindDataOpts}}', '{{BindDataTxt}}'];
        $replaces = [$bindDataOpts, $bindDataTxt];

        if ($this->isAddonMaster) {
            $searchs[]  = "{{UseModel}}";
            $replaces[] = "app\\addon{$this->controlPrefix}\model";
        } else {
            $searchs[]  = "{{UseModel}}";
            $replaces[] = "vandles\model";
        }
        $tplContent = str_replace($searchs, $replaces, $tplContent);

        file_put_contents($fileName, $tplContent);
        return "新建：" . $fileName;
    }

    /**
     * 创建插件
     * @return array
     */
    public function buildAddon($title) {
        if(!$this->isAddon) VException::throw("不是创建插件操作");
        $result = [];
        // 插件名称
        $name = $this->controlPrefix;
        $appRoot = app()->getAppPath();

        // Addon.tpl
        $filePath = $appRoot . "addon" . $name . DIRECTORY_SEPARATOR;
        $fileName = ucfirst($name) . ".php";
        $fullName = $filePath . $fileName;
        if (is_file($fullName) && !$this->isForce) $result[$fileName] = "保持：" . $fullName;
        else {
            if (!is_dir($filePath)) mkdir($filePath, 0777, true);

            $tpl = __DIR__ . "/template/addon/Addon.tpl";
            if (!is_file($tpl)) VException::throw("模板文件Addon.tpl不存在");
            $fileContent = file_get_contents($tpl);

            $searchs     = [
                '{{name}}', '{{Name}}'
            ];
            $replaces    = [
                $name,
                ucfirst($name)
            ];
            $fileContent = str_replace($searchs, $replaces, $fileContent);
            file_put_contents($fullName, $fileContent);
            $result[$fileName] = "新建：" . $fullName;
        }

        // info.yml
        $filePath = $appRoot . "addon" . $name . DIRECTORY_SEPARATOR;
        $fileName = "info.yml";
        $fullName = $filePath . $fileName;
        if (is_file($fullName) && !$this->isForce) $result[$fileName] = "保持：" . $fullName;
        else{
            if (!is_dir($filePath)) mkdir($filePath, 0777, true);
            $tpl = __DIR__ . "/template/addon/info.yml";
            if (!is_file($tpl)) VException::throw("模板文件info.yml不存在");
            $fileContent = file_get_contents($tpl);

            $searchs     = [
                '{{name}}', '{{title}}'
            ];
            $replaces    = [
                $name, $title
            ];
            $fileContent = str_replace($searchs, $replaces, $fileContent);
            file_put_contents($fullName, $fileContent);
            $result[$fileName] = "新建：" . $fullName;
        }

        // index.tpl
        $filePath = $appRoot . "addon" . $name . DIRECTORY_SEPARATOR . "controller" . DIRECTORY_SEPARATOR;
        $fileName = "Index.php";
        $fullName = $filePath . $fileName;
        if (is_file($fullName) && !$this->isForce) $result[$fileName] = "保持：" . $fullName;
        else{
            if (!is_dir($filePath)) mkdir($filePath, 0777, true);
            $tpl = __DIR__ . "/template/addon/index.tpl";
            if (!is_file($tpl)) VException::throw("模板文件index.tpl不存在");
            $fileContent = file_get_contents($tpl);

            $searchs     = [
                '{{name}}', '{{Name}}'
            ];
            $replaces    = [
                $name, ucfirst($name)
            ];
            $fileContent = str_replace($searchs, $replaces, $fileContent);
            file_put_contents($fullName, $fileContent);
            $result[$fileName] = "新建：" . $fullName;
        }

        // index.html
        $filePath = $appRoot . "addon" . $name . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "index" . DIRECTORY_SEPARATOR;
        $fileName = "index.html";
        $fullName = $filePath . $fileName;
        if (is_file($fullName) && !$this->isForce) $result[$fileName] = "保持：" . $fullName;
        else{
            if (!is_dir($filePath)) mkdir($filePath, 0777, true);
            $tpl = __DIR__ . "/template/addon/index.html";
            if (!is_file($tpl)) VException::throw("模板文件index.html");
            $fileContent = file_get_contents($tpl);

            $searchs     = [
                '{{name}}'
            ];
            $replaces    = [
                $name
            ];
            $fileContent = str_replace($searchs, $replaces, $fileContent);
            file_put_contents($fullName, $fileContent);
            $result[$fileName] = "新建：" . $fullName;
        }

        // js && css
        $filePath = app()->getRootPath() . "public" . DIRECTORY_SEPARATOR . "static" . DIRECTORY_SEPARATOR . "addon" . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        $fileName = $name . ".js";
        $fullName = $filePath . $fileName;
        if (is_file($fullName) && !$this->isForce) $result[$fileName] = "保持：" . $fullName;
        else{
            if (!is_dir($filePath)) mkdir($filePath, 0777, true);
            file_put_contents($fullName, "");
            $result[$fileName] = "新建：" . $fullName;
        }
        $fileName = $name . ".css";
        $fullName = $filePath . $fileName;
        if (is_file($fullName) && !$this->isForce) $result[$fileName] = "保持：" . $fullName;
        else{
            if (!is_dir($filePath)) mkdir($filePath, 0777, true);
            file_put_contents($fullName, "");
            $result[$fileName] = "新建：" . $fullName;
        }

        // install.sql && test.sql
        $filePath = $appRoot . "addon" . $name . DIRECTORY_SEPARATOR;
        $fileName = "install.sql";
        $fullName = $filePath . $fileName;
        if (is_file($fullName) && !$this->isForce) $result[$fileName] = "保持：" . $fullName;
        else{
            if (!is_dir($filePath)) mkdir($filePath, 0777, true);
            $tpl = __DIR__ . "/template/addon/{$fileName}";
            if (is_file($tpl)){
                $fileContent = file_get_contents($tpl);
                file_put_contents($fullName, $fileContent);
                $result[$fileName] = "新建：" . $fullName;
            }
        }
        $fileName = "test.sql";
        $fullName = $filePath . $fileName;
        if (is_file($fullName) && !$this->isForce) $result[$fileName] = "保持：" . $fullName;
        else{
            if (!is_dir($filePath)) mkdir($filePath, 0777, true);
            $tpl = __DIR__ . "/template/addon/{$fileName}";
            if (is_file($tpl)){
                $fileContent = file_get_contents($tpl);
                file_put_contents($fullName, $fileContent);
                $result[$fileName] = "新建：" . $fullName;
            }
        }
        return $result;
    }

    private function getServiceBindData($v, $tableName): array {
        $field = $v['field'];

        $modelName = $this->pascal2big($tableName);
        $optsName  = $this->pascal2big($field);

        $opts = "#{$field}s = {$modelName}Model::get{$optsName}s();";
        $opts = str_replace("#", "$", $opts);

        $txt = "#v['{$field}_txt'] = #{$field}s[#v['{$field}']]??'未知';";
        $txt = str_replace("#", "$", $txt);

        return [$opts, $txt];
    }


    /**
     * 返回要渲染的内容 和 文件名
     * @param string $tableName
     * @param $name
     * @return array
     * @throws \Exception
     */
    private function buildToExtend(string $tableName, $name): array {
        $tableName = $this->pascal2big($tableName);
        $root      = app()->getRootPath();

        if ($this->isAddonMaster) {
            if (empty($this->controlPrefix)) throw new \Exception("插件名称不能为空");
            $namespace = "app\\addon{$this->controlPrefix}\\{$name}";
            $filePath  = $root . $namespace . DIRECTORY_SEPARATOR;

        } else {
            $namespace = "vandles\\{$name}";
            $filePath  = $root . "extend" . DIRECTORY_SEPARATOR . $namespace . DIRECTORY_SEPARATOR;
        }

        $className = $tableName . ucfirst($name);

        $fileName = $filePath . $className . ".php";

        if (file_exists($fileName) && !$this->isForce) {
            // $fileName = "保持：" . $fileName;
            $tplContent = "is_exist";
            return compact('fileName', 'tplContent');
        }

        $tplFile = __DIR__ . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . "{$name}.tpl";
        if (!file_exists($tplFile)) VException::throw("{$name}模板文件不存在");

        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }
        $tplContent = file_get_contents($tplFile);
        $tplContent = str_replace(['{{Namespace}}', '{{ClassName}}', '{{TableName}}'], [$namespace, $className, $tableName], $tplContent);

        return compact('fileName', 'tplContent');
    }

    /**
     * 帕斯卡转大驼峰
     * @param $str
     * @return array|string|string[]
     */
    private function pascal2big($str) {
        $str = str_replace('_', ' ', $str);
        $str = ucwords($str);
        $str = str_replace(' ', '', $str);
        return $str;
    }

    /**
     * 帕斯卡转小驼峰
     * @param $str
     * @return string
     */
    private function pascal2small($str) {
        $str = str_replace('_', ' ', $str);
        $str = ucwords($str);
        $str = str_replace(' ', '', $str);
        return lcfirst($str);
    }

    public function getColumns($tableName) {
        if (!Db::connect()) VException::throw("数据库未连接");

        if ($this->tablePrefix) $tableName = $this->tablePrefix . '_' . $tableName;
        // 获取表信息
        // $tableInfo = Db::getTableInfo($tableName);
        $comments  = $this->parseColComment($tableName);
        $lengths   = $this->parseColLength($tableName);

        $columns = [];
        foreach ($comments as $k => $v) {
            $item        = [
                'field'  => $v['field'],
                'title'  => $v['title']??"",
                'type'   => $v['type']??"",
                'length' => $lengths[$k]??0,
                'remark' => $v['remark']??"",
                'opts'   => $v['opts']??[],
            ];
            $columns[$k] = $item;
        }
        return $columns;
    }

    /**
     * 获取列注释
     * @param $tableName
     * @return array
     */
    private function parseColComment($tableName) {
        $tableComment = Db::query("select column_name, column_comment from information_schema.columns where table_name = '{$tableName}'");
        $comments     = array_column($tableComment, 'column_comment', 'column_name');
        foreach ($comments as $key => $value) {
            // 去除$value的所有空格
            $value = trim($value);
            $value = str_replace(' ', '', $value);
            $sub = json_decode($value, true);

            $sub['field'] = $key;
            $comments[$key] = $sub;
        }
        return $comments;
    }

    private function parseColLength($tableName) {
        $tableComment = Db::query("select column_name, character_maximum_length from information_schema.columns where table_name = '{$tableName}'");
        $lengths      = array_column($tableComment, 'character_maximum_length', 'column_name');
        return $lengths;
    }

    /**
     * 表单页面添加项目 - 文本
     * @param array $title
     * @return string
     */
    private function getFormItemText(array $v) {
        $field    = $v['field'];
        $title    = $v['title'];
        $valueTxt = "{" . "$" . "vo.{$field}|default=''}";
        if (!empty($v['remark'])) $remark = "<div class='help-block'>{$v['remark']}</div>";
        else $remark = "";
        return <<<EOT
        <label class="layui-form-item block relative">
            <span class="help-label"><b>{$title}</b></span>
            <input class="layui-input" name="{$field}" placeholder="请输入{$title}" value="{$valueTxt}">
            {$remark}
        </label>
EOT;
    }

    /**
     * 表单页面添加项目 - 日期
     * @param string $field
     * @param string $title
     * @return string
     */
    private function getFormItemDatetime(array $v) {
        $field    = $v['field'];
        $title    = $v['title'];
        $valueTxt = "{" . "$" . "vo.{$field}|default=now()}";

        if (!empty($v['remark'])) $remark = "<div class='help-block'>{$v['remark']}</div>";
        else $remark = "";
        return <<<EOT
        <label class="layui-form-item block relative">
            <span class="help-label"><b>{$title}</b></span>
            <input class="layui-input" data-date-input="datetime" name="{$field}" placeholder="请输入{$title}" value="{$valueTxt}">
            {$remark}
        </label>
EOT;
    }

    /**
     * 表单页面添加项目 - 数字
     * @param string $field
     * @param string $title
     * @return string
     */
    private function getFormItemNumber(array $v) {
        $field    = $v['field'];
        $title    = $v['title'];
        $valueTxt = "{" . "$" . "vo.{$field}|default=''}";
        if (!empty($v['remark'])) $remark = "<div class='help-block'>{$v['remark']}</div>";
        else $remark = "";
        return <<<EOT
        <label class="layui-form-item block relative">
            <span class="help-label"><b>{$title}</b></span>
            <input class="layui-input" type="number" name="{$field}" placeholder="请输入{$title}" value="{$valueTxt}">
            {$remark}
        </label>
EOT;
    }

    /**
     * 表单页面添加项目 - 单图片
     * @param string $field
     * @param string $title
     * @return string
     */
    private function getFormItemPic(array $v) {
        $field    = $v['field'];
        $title    = $v['title'];
        $valueTxt = "{" . "$" . "vo.{$field}|default=''}";
        if (!empty($v['remark'])) $remark = "<div class='help-block'>{$v['remark']}</div>";
        else $remark = "";
        return <<<EOT
        <div class="layui-form-item block relative">
            <span class="help-label"><b>{$title}</b></span>
            <div><input type="hidden" name="{$field}" value="{$valueTxt}"></div>
            {$remark}
            <script>$("[name={$field}]").uploadOneImage()</script>
        </div>
EOT;
    }

    /**
     * 列表页面添加搜索项目 - 文本
     * @param int $field
     * @param $title
     * @return string
     */
    private function getIndexSearchItemText(string $field, string $title): string {
        $valueTxt = "{" . "$" . "get.{$field}|default=''}";
        return <<<EOT
    <div class="layui-form-item layui-inline">
        <label class="layui-form-label" title="{$title}">{$title}</label>
        <label class="layui-input-inline">
            <input class="layui-input" name="{$field}" placeholder="请输入{$title}" value="{$valueTxt}">
        </label>
    </div>
EOT;
    }

    /**
     * 列表页面添加搜索项目 - 日期
     * @param int $field
     * @param $title
     * @return string
     */
    private function getIndexSearchItemDateRange(string $field, string $title): string {
        $valueTxt = "{" . "$" . "get.{$field}|default=''}";
        return <<<EOT
    <div class="layui-form-item layui-inline">
        <label class="layui-form-label" title="{$title}">{$title}</label>
        <label class="layui-input-inline">
            <input class="layui-input" data-date-range="datetime" name="{$field}" placeholder="请输入{$title}" value="{$valueTxt}">
        </label>
    </div>
EOT;
    }

    private function getIndexSearchItemSelect($v) {
        $field    = $v['field'];
        $title    = $v['title'];
        // $valueTxt = "{" . "$" . "get.{$field}|default=''}";
        // if (!empty($v['remark'])) $remark = "<div class='help-block'>{$v['remark']}</div>";
        // else $remark = "";
        $opts = [];
        if (isset($v['opts'])) {
            foreach ($v['opts'] as $kk => $vv) {
                $selectStr = "{if isset(#get.{$field}) && #get.{$field} == '{$kk}'}selected{/if}";
                $selectStr = str_replace("#", "$", $selectStr);
                if (is_array($vv)) {
                    $opts[] = "<option value='{$kk}' {$selectStr}>{$vv['title']}</option>";
                } else {
                    $opts[] = "<option value='{$kk}' {$selectStr}>{$vv}</option>";
                }
            }
        }
        $opts = implode("\n                ", $opts);
        return <<<EOT
    <div class="layui-form-item layui-inline">
        <label class="layui-form-label" title="{$title}">{$title}</label>
        <label class="layui-input-inline">
            <select name="{$field}" lay-filter="search">
                <option value="">请选择</option>
                {$opts}
            </select>
        </label>
    </div>
EOT;
    }

    /**
     * 列表页面添加表格项目 - 文本
     * @param int $field
     * @param $title
     * @return void
     */
    private function getIndexItemText($v): string {
        $title = $v['title'];
        $field = $v['field'];
        return "                cols.push({field: '{$field}', title: '{$title}', align: 'left'});";
    }

    /**
     * 列表页面添加表格项目 - 选择
     * @param int $field
     * @param $title
     * @return void
     */
    private function getIndexItemSelect($v): string {
        $title = $v['title'];
        $field = $v['field'];
        return "                cols.push({field: '{$field}_txt', title: '{$title}', align: 'left'});";
    }
    /**
     * 列表页面添加表格项目 - 单图片
     * @param int $field
     * @param $title
     * @return void
     */
    private function getIndexItemPic($v): string {
        $title = $v['title'];
        $field = $v['field'];
        $src = '${d.' . $field . '}';
        return <<<EOT
                cols.push({field: '{$field}', title: '{$title}', align: 'left', templet: function (d) {
                    if(!d.{$field}) return '';
                    return `<div class="headimg headimg-no headimg-xs" data-lazy-src="{$src}" data-tips-image data-tips-hover></div>`;
                }});
EOT;
    }

    private function getFormItemSelect($v) {
        $field    = $v['field'];
        $title    = $v['title'];
        $valueTxt = "{" . "$" . "get.{$field}|default=''}";
        if (!empty($v['remark'])) $remark = "<div class='help-block'>{$v['remark']}</div>";
        else $remark = "";
        $opts = [];
        if (isset($v['opts'])) {
            foreach ($v['opts'] as $kk => $vv) {
                $selectStr = "{if isset(#vo.{$field}) && #vo.{$field} == '{$kk}'}selected{/if}";
                $selectStr = str_replace("#", "$", $selectStr);
                if (is_array($vv)) {
                    $opts[] = "<option value='{$kk}' {$selectStr}>{$vv['title']}</option>";
                } else {
                    $opts[] = "<option value='{$kk}' {$selectStr}>{$vv}</option>";
                }
            }
        }
        $opts = implode("\n                ", $opts);
        return <<<EOT
        <label class="layui-form-item block relative">
            <span class="help-label"><b>{$title}</b></span>
            <select class="layui-select" name="{$field}">
                <option value=""></option>
                {$opts}
            </select>
            {$remark}
        </label>
EOT;
    }
}