<?php
namespace Common\Support;

/**
 * Excel导出
 *
 * @author Flc <2016-08-23 09:31:03>
 * @example  
 *
 *      use Common\Support\Excel;
 *
 *      $excel = new Excel;
 *      // 导出文件
 *      $excel->setTitle([])->setData([])->render('文件名.xls');
 *      
 *      // 保存
 *      $excel->setTitle([])->setData([])->save(ROOT_PATH . '文件名.xls');
 */
class Excel
{
    /**
     * Excel对象
     * @var [type]
     */
    protected $oExcel;

    /**
     * 文件名
     * @var string
     */
    protected $filename;

    /**
     * 列标题
     * @var array
     */
    protected $title = [];

    /**
     * 列数据
     * @var array
     */
    protected $data = [];

    /**
     * sheet标题
     * @var string
     */
    protected $sheet_title = 'sheet1';

    /**
     * 初始化
     */
    public function __construct()
    {
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.Writer.Excel2007');

        // 创建excel对象
        $this->oExcel = new \PHPExcel();

        // 默认文件名
        $this->filename =  'export_' . date('Y-m-d') . '.xls';
    }

    /**
     * 设置文件名
     * @param [type] $value [description]
     */
    public function setFileName($value)
    {
        $this->filename = $value;

        return $this;
    }

    /**
     * 设置标题
     */
    public function setTitle($value = [])
    {
        $this->title = $value;

        return $this;
    }

    /**
     * 设置excel的数据
     * @param [type] $data [description]
     */
    public function setData($value = [])
    {
        $this->data = $value;

        return $this;
    }

    /**
     * 设置表sheet名称
     * @param string $value 
     */
    public function setSheetTitle($value)
    {
        $this->sheet_title = $value;

        return $this;
    }

    /**
     * 渲染输出文件
     * @param  string $filename 文件名
     * @return [type]           [description]
     */
    public function render($filename = '')
    {
        if (! empty($filename)) {
            $this->filename = $filename;
        }

        // 组合数据
        $this->combine();

        // 保存excel—2007格式
        $oWriter = new \PHPExcel_Writer_Excel2007($this->oExcel);

        // 输出
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$this->filename.'"');
        header('Cache-Control: max-age=0');

        $oWriter->save('php://output');
    }

    /**
     * 保存为文件
     * @param  string $filename 文件路径
     * @return [type]           [description]
     */
    public function save($filename = '')
    {
        if (! empty($filename)) {
            $this->filename = $filename;
        }

        // 组合数据
        $this->combine();

        // 保存excel—2007格式
        $oWriter = new \PHPExcel_Writer_Excel2007($this->oExcel);

        $oWriter->save($this->filename);
    }

    /**
     * 组合数据
     * @return [type] [description]
     */
    protected function combine()
    {
        // 设置当前的sheet
        $this->oExcel->setActiveSheetIndex(0);
        // 设置sheet的name
        $this->oExcel->getActiveSheet()->setTitle($this->sheet_title);

        // 组合列头
        $dataRow = 1; // data数据开始的行号
        if (count($this->title) > 0) {
            $titCol = 'A';
            foreach ($this->title as $v) {
                $this->oExcel->getActiveSheet()->setCellValue($titCol . '1', $v);
                $titCol++;
            }

            $dataRow = 2;
        }

        // 组合data数据
        foreach ($this->data as $item) {
            $dataCol = 'A';
            foreach ($item as $v) {
                $this->oExcel->getActiveSheet()->setCellValue($dataCol . $dataRow, $v);
                $dataCol++;
            }

            $dataRow++;
        }
    }
    
}