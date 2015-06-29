<?php
/**
 * Excel帮助类
 * @author baol
 * @create_time 2015-04-02
 */
namespace mysoft\helpers;
require VENDOR_PATH.'/PHPExcel/PHPExcel.php';

class Excel
{
	//Excel对象
	private $_objPHPExcel;
	
	//列签
	private $letter =['A' , 'B' , 'C' , 'D' , 'E' , 'F' , 'G' , 'H' , 'I' , 'J' , 'K' ,'L', 'M' , 'N','O', 'P' , 'Q' , 'R' , 'S' , 'T' , 'U' , 'V' , 'W' , 'X' , 'Y' , 'Z' ,  'AA' , 'AB' , 'AC' , 'AD' , 'AE' , 'AF' , 'AG' , 'AH' , 'AI' , 'AJ' , 'AK' ,'AL', 'AM' , 'AN' ,'AO', 'AP' , 'AQ' , 'AR' , 'AS' , 'AT' , 'AU' , 'AV' , 'AW' , 'AX' , 'AY' , 'AZ' ];
	
	//表头样式
	private $_headstyle = [
		'font-bold' => true,
		'font-size' => 10,
		'font-color' => '000000', //RGB
		'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
		'vertical' => \PHPExcel_style_Alignment::VERTICAL_CENTER,
		'border-style' => \PHPExcel_Style_Border::BORDER_THIN,
		'border-color' => '000000', //RGB
		'background-color' => 'cccccc',
		'row-height' => 25,
		'column-width' => 20,
	];
	
	//单元格样式
	private $_cellstyle = [
		'font-bold' => false,
		'font-size' => 10,
		'font-color' => '000000', //RGB
		'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
		'vertical' => \PHPExcel_style_Alignment::VERTICAL_CENTER,
		'border-style' => \PHPExcel_Style_Border::BORDER_THIN,
		'border-color' => '000000', //RGB
		'background-color' => 'ffffff',
		'row-height' => 15
	];
	
	//模版描述信息样式
	private $_desstyle = [
		'font-bold' => false,
		'font-size' => 10,
		'font-color' => '000000', //RGB
		'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
		'vertical' => \PHPExcel_style_Alignment::VERTICAL_TOP,
		'border-style' => \PHPExcel_Style_Border::BORDER_THIN,
		'border-color' => '000000', //RGB
		'background-color' => '00b0f0',
		'row-height' => 50
	];
	
	//配置信息
	private $_config = [
		'font' => '微软雅黑',
		'font-size' => 10,
		'row-height' => '15',
	];
	
	//excel属性
	private $_property = [
		'creator' => '',
		'lastModifiedBy' => 'yunke',
		'title' => '',
		'subject' => '',
		'description' => '',
		'keywords' => '',
		'category' => ''
	];
	
	/**
	 * 构造函数
	 */
	function __construct(){
		$this->_objPHPExcel = new \PHPExcel();	
		$this->_init();
	}
	
	/**
	 * 重设表头样式
	 * @author baol
	 * @param string $key 键
	 * @param string $value 值
	 */
	public function setHederStyle($key,$value)
	{
		if(isset($this->_headstyle[$key])){
			$this->_headstyle[$key] = $value;
		}
		return $this;
	}
	
	/**
	 * 获取excel属性
	 * @author baol
	 */
	public function getPropertys($filename)
	{
		$objReader =  \PHPExcel_IOFactory::createReader ('Excel2007');
		$this->_objPHPExcel = $objReader->load($filename);
		return $this->_objPHPExcel;
	}
	
	/**
	 * 重设单元格样式
	 * @author baol
	 * @param string $key 键
	 * @param string $value 值
	 */
	public function setCellStyle($key,$value)
	{
		if(isset($this->_cellstyle[$key])){
			$this->_cellstyle[$key] = $value;
		}
		return $this;
	}

	/**
	 * 重设模版描述样式
	 * @author baol
	 * @param string $key 键
	 * @param string $value 值
	 */
	public function setTempDesStyle($key,$value)
	{
		if(isset($this->_desstyle[$key])){
			$this->_desstyle[$key] = $value;
		}
		return $this;
	}
	
	/**
	 * @author baol
	 * 设置excel属性
	 */
	public function setProperty($key,$value)
	{
		if(isset($this->_property[$key])){
			$this->_property[$key] = $value;
		}
		return $this;
	}
	
	/**
	 * 导出excel
	 * @author baol
	 * @param string $filename 文件名
	 * @param string $sheetname sheet名
	 * @param array $data 数据 例如：$data = [['姓名' => '张三', '年龄' => '50'],['姓名' => '李四', '年龄' => '60']]
	 */
	public function export($filename, $sheetname, $data, $description = '', $hasData = true)
	{	
		if(!strrchr($filename,'.xlsx')){
			$filename = $filename.'.xlsx';
		}
		if(empty($sheetname)){
			$sheetname = 'sheet1';
		}
		$filename = iconv('utf-8', 'gbk', $filename);
		
		//sheet名称
		$this->_objPHPExcel->getActiveSheet()->setTitle($sheetname);
		$headindex = 1;
		$columncount = count($data[0]);

		if(!empty($description)){
			$this->_setTemplateDes($columncount, $description);
			$headindex++;
		}
		
		//设置列头
		$this->_setHeader($data[0], $headindex);
		
		//设置数据
		if($hasData){
			$this->_setData($data, $headindex+1);
		}
				
		//从浏览器直接输出
		$objWriter = \PHPExcel_IOFactory::createWriter($this->_objPHPExcel, 'Excel2007');
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type: application/vnd.ms-excel;");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header("Content-Disposition:attachment;filename=".$filename);
		header("Content-Transfer-Encoding:binary");
		$objWriter->save("php://output");
	}
			
	/**
	 * 读取excel数据
	 * @author baol
	 * @param string $filename excle路径
	 * @param int 表头的索引
	 * @param int $columnIndex 第一列索引
	 * @return array
	 */
	public function read($filename, $headIndex = 1, $columnIndex = 0)
	{
		//读取excel数据
		$objReader = \PHPExcel_IOFactory::createReader ('Excel2007');
		$this->_objPHPExcel = $objReader->load($filename);
		$this->_objPHPExcel->setActiveSheetIndex(0);
		$excel = $this->_objPHPExcel->getActiveSheet();
		$allRowCount = $excel->getHighestRow();
		$allColumnsCount = \PHPExcel_Cell::columnIndexFromString($excel->getHighestColumn());
	
		//列头
		$headRow = [];
		for($j=$columnIndex;$j<$allColumnsCount;$j++){
			$headRow[$j] = $this->_fliter($excel->getCellByColumnAndRow($j,$headIndex)->getValue());
		}
		
		//数据
		$res = [];
		for($i=$headIndex+1; $i<=$allRowCount; $i++){
			$col = [];
			$hasdata = false;
			for($k=$columnIndex; $k<$allColumnsCount; $k++){
				$value = $this->_fliter($excel->getCellByColumnAndRow($k,$i)->getValue());
				if(!empty($value)){
					$hasdata = true;
				}
				$col[$headRow[$k]] = $value;
			}
			if($hasdata){
				$res[$i] = $col;
			}
		}
		
		return $res;
	} 
	
	/**
	 *为excel添加备注列信息
	 *@author baol
	 *@param $filename excel路径
	 *@param $headindex 表头索引
	 *@param $columnIndex 第一列索引
	 *@param $remarkData 备注列数据 ['行号' => '备注信息']
	 *@param $newfilename 保存的新文件路径
	 */
	public function addRemarkColumn($filename, $headindex = 1, $columnIndex = 0, $remarkData, $newfilename)
	{
		//读取excel数据
		$objReader = \PHPExcel_IOFactory::createReader ('Excel2007');
		$this->_objPHPExcel = $objReader->load($filename);
		$this->_objPHPExcel->setActiveSheetIndex(0);
		$excel = $this->_objPHPExcel->getActiveSheet();
		
		$allRowCount = $excel->getHighestRow();
		$allColumnsCount = \PHPExcel_Cell::columnIndexFromString($excel->getHighestColumn());

		$excel->unmergeCells('A1:'.$this->letter[$allColumnsCount-1].'1');
		
		//表头
		$style = $excel->getStyle($this->letter[$allColumnsCount].$headindex);
		$style->getFont()->setBold($this->_headstyle['font-bold']);
		$style->getFont()->setSize($this->_headstyle['font-size']);
		$style->getFont()->getColor()->setRGB($this->_headstyle['font-color']);
		$style->getAlignment()->setHorizontal($this->_headstyle['horizontal']);
		$style->getAlignment()->setVertical($this->_headstyle['vertical']);
		$style->getBorders()->getOutline()->setBorderStyle($this->_headstyle['border-style']);
		$style->getBorders()->getOutline()->getColor()->setRGB($this->_headstyle['border-color']);
		$style->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
		$style->getFill()->getStartColor()->setRGB($this->_headstyle['background-color']);
		$excel->getColumnDimension($this->letter[$allColumnsCount])->setWidth($this->_headstyle['column-width']);
		$excel->setCellValue($this->letter[$allColumnsCount].$headindex, '备注');
		
		foreach ($remarkData as $k => $v){
			for ($i=$columnIndex; $i<$allColumnsCount; $i++){
				//备注行添加背景颜色样式
				$style = $excel->getStyle($this->letter[$i].$k);
				$style->getFill()->getStartColor()->setRGB($this->_cellstyle['background-color']);
			}
			
			//备注信息
			$style = $excel->getStyle($this->letter[$allColumnsCount].$k);
			$style->getFont()->setBold($this->_cellstyle['font-bold']);
			$style->getFont()->setSize($this->_cellstyle['font-size']);
			$style->getFont()->getColor()->setRGB($this->_cellstyle['font-color']);
			$style->getAlignment()->setHorizontal($this->_cellstyle['horizontal']);
			$style->getAlignment()->setVertical($this->_cellstyle['vertical']);
			$style->getBorders()->getOutline()->setBorderStyle($this->_cellstyle['border-style']);
			$style->getBorders()->getOutline()->getColor()->setRGB($this->_cellstyle['border-color']);
			$style->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
			$style->getFill()->getStartColor()->setRGB($this->_cellstyle['background-color']);
			$excel->setCellValue($this->letter[$allColumnsCount].$k, $v);
			$excel->getRowDimension($k)->setRowHeight($this->_cellstyle['row-height']);
		}
		
		//合并表头
		$excel->mergeCells('A1:'.$this->letter[$allColumnsCount].'1');
		
		$objWriter = \PHPExcel_IOFactory::createWriter($this->_objPHPExcel, "Excel2007");
		$objWriter->save ($newfilename);
	}
	
	/**
	 * excel模版下载，考虑到模版中有些列有数据校验，直接抽成一个独立的方法
	 * @author baol
	 * @param string $filename
	 * @param string $sheetname
	 * @param array $data 表头数据 ['姓名' => '', '年龄' => '', '性别' => ['formula1' => "男,女"]]
	 * @param string $description 模版描述信息，换行用\n
	 */
	public function downloadTemplate($filename, $sheetname = '', $data, $rowcount, $description = '')
	{
		if(!strrchr($filename,'.xlsx')){
			$filename = $filename.'.xlsx';
		}
		if(empty($sheetname)){
			$sheetname = 'sheet1';
		}
		$filename = iconv('utf-8', 'gbk', $filename);
			
		$this->_setProperties();
		
		//制定工作簿
		$excel = $this->_objPHPExcel->getActiveSheet();
		$excel->setTitle($sheetname);
		$headIndex = 1;

		//添加模版描述文件
		if(!empty($description)){
			$columncount = count($data);
			$this->_setTemplateDes($columncount, $description);
			$headIndex++;
		}
		
		//设置列头
		$this->_setHeader($data,$headIndex);
			
		//设置数据
		for($i=$headIndex+1; $i<$rowcount; $i++){
			$columIndex = 0;
			$excel->getRowDimension($i)->setRowHeight($this->_cellstyle['row-height']);
			foreach ($data as $k => $v){
				//样式
				$style = $excel->getStyle($this->letter[$columIndex].$i);
				$style->getFont()->setBold($this->_cellstyle['font-bold']);
				$style->getFont()->setSize($this->_cellstyle['font-size']);
				$style->getFont()->getColor()->setRGB($this->_cellstyle['font-color']);
				$style->getAlignment()->setHorizontal($this->_cellstyle['horizontal']);
				$style->getAlignment()->setVertical($this->_cellstyle['vertical']);
				$style->getBorders()->getOutline()->setBorderStyle($this->_cellstyle['border-style']);
				$style->getBorders()->getOutline()->getColor()->setRGB($this->_cellstyle['border-color']);
				$style->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
				$style->getFill()->getStartColor()->setRGB($this->_cellstyle['background-color']);
				
				//数据有效性
				if(is_array($v) && isset($v['formula1']))
				{
					$style->getAlignment()->setWrapText(true);
					$validation = $excel->getCell($this->letter[$columIndex].$i)->getDataValidation();
					$validation->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)
								->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
								->setAllowBlank(false)
								->setShowInputMessage(true)
								->setShowErrorMessage(true)
								->setShowDropDown(true)
								->setErrorTitle('输入的值有误')
								->setError('您输入的值不在下拉框列表内.')
								->setPromptTitle('设备类型')
								->setFormula1('"'.$v['formula1'].'"');
				}
				$columIndex++;
			}			
		}

		//从浏览器直接输出
		$objWriter = \PHPExcel_IOFactory::createWriter($this->_objPHPExcel, 'Excel2007');
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type: application/vnd.ms-excel;");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header("Content-Disposition:attachment;filename=".$filename);
		header("Content-Transfer-Encoding:binary");
		$objWriter->save("php://output");
	}
	
	/**
	 * 直接浏览器输出excel
	 * @author baol
	 * @param $filename 输出excel名称
	 * @param $path     excel路径
	 */
	public function output($filename, $path){
		if(!strrchr($filename,'.xlsx')){
			$filename = $filename.'.xlsx';
		}
		
		$objReader = \PHPExcel_IOFactory::createReader ('Excel2007');
		$this->_objPHPExcel = $objReader->load($path);
		$this->_objPHPExcel->setActiveSheetIndex(0);
		
		//从浏览器直接输出
		$objWriter = \PHPExcel_IOFactory::createWriter($this->_objPHPExcel, 'Excel2007');
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type: application/vnd.ms-excel;");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header("Content-Disposition:attachment;filename=".$filename);
		header("Content-Transfer-Encoding:binary");
		$objWriter->save("php://output");
	}
	
	/**
	 * 初始化基本信息
	 * @author baol
	 */
	private function _init()
	{
		//制定工作簿
		$this->_objPHPExcel->setActiveSheetIndex(0);
	
		// 设置默认字体和大小
		$this->_objPHPExcel->getDefaultStyle()->getFont()->setName($this->_config['font']);
		$this->_objPHPExcel->getDefaultStyle()->getFont()->setSize($this->_config['font-size']);
		$this->_objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight($this->_config['row-height']);
	}
	
	/**
	 * 设置excel默认属性
	 * @author baol
	 */
	private function _setProperties() {
		$this->_objPHPExcel->getProperties()->setCreator($this->_property['creator']);
		$this->_objPHPExcel->getProperties()->setTitle($this->_property['title']);
		$this->_objPHPExcel->getProperties()->setSubject($this->_property['subject']);
		$this->_objPHPExcel->getProperties()->setDescription($this->_property['description']);
		$this->_objPHPExcel->getProperties()->setKeywords($this->_property['keywords']);
	}
	
	/**
	 * 设置表头
	 * @author baol
	 * @param array $data 数据
	 */
	private function _setHeader($data, $headindex)
	{
		$excel = $this->_objPHPExcel->getActiveSheet();
		$excel->getRowDimension($headindex)->setRowHeight($this->_headstyle['row-height']);
		//样式
		$styleArray = [
			'borders' => [
				'allborders' => [
				'style' => $this->_headstyle['border-style'],
				'color' => ['argb' => $this->_headstyle['border-color']],
				],
			],
			'font' => [
				'bold' => $this->_headstyle['font-bold'],
				'size' => $this->_headstyle['font-size'],
				'color' => ['argb' => $this->_headstyle['font-color']]
			],
			'alignment' => [
				'horizontal' => $this->_headstyle['horizontal'],
				'vertical' => $this->_headstyle['vertical']
			],
			'fill' => [
				'type' => \PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => ['argb' => $this->_headstyle['background-color']]
			]
		];
		$columIndex = 0;
		$columCount = count($data) - 1;
		$excel->getStyle($this->letter[$columIndex].$headindex.':'.$this->letter[$columCount].$headindex)->applyFromArray($styleArray);
		//数据
		foreach ($data as $k => $v){
			$excel->getColumnDimension($this->letter[$columIndex])->setWidth($this->_headstyle['column-width']);
			$excel->setCellValue($this->letter[$columIndex].$headindex, $k);
			$columIndex++;
		}
	}
	
	/**
	 * 设置单元格数据
	 * @author baol
	 * @param array $data 数据文件
	 */
	private function _setData($data, $dataIndex)
	{
		$excel = $this->_objPHPExcel->getActiveSheet();
		//样式
		$styleArray = [
			'borders' => [
				'allborders' => [
					'style' => $this->_cellstyle['border-style'],
					'color' => ['argb' => $this->_cellstyle['border-color']],
				],
			],
			'font' => [
				'bold' => $this->_cellstyle['font-bold'],
				'size' => $this->_cellstyle['font-size'],
				'color' => ['argb' => $this->_cellstyle['font-color']]
			],
			'alignment' => [
				'horizontal' => $this->_cellstyle['horizontal'],
				'vertical' => $this->_cellstyle['vertical']
			],
			'fill' => [
				'type' => \PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => ['argb' => $this->_cellstyle['background-color']]
			]
		];
		$columCount = count($data[0]) - 1;
		$rowCount = count($data) + 1;
		$excel->getStyle($this->letter[0].$dataIndex.':'.$this->letter[$columCount].$rowCount)->applyFromArray($styleArray);
		
		//数据
		foreach ($data as $k => $v){
			$columIndex = 0;
			foreach ($v as $dk => $dv){
				$excel->setCellValue($this->letter[$columIndex].$dataIndex, $dv);
				$excel->getRowDimension($dataIndex)->setRowHeight($this->_cellstyle['row-height']);
				$columIndex++;
			}
			$dataIndex++;
		}
	}
	
	/**
	 * 处理value为 \PHPExcel_RichText 对象
	 * @author baol
	 * 解决copy单元格样式过来导致的获取单元格内容问题
	 * @param  string|object<\PHPExcel_RichText>  $value
	 * @return string
	 */
	private function _fliter($value)
	{
		if($value instanceof \PHPExcel_RichText){
			$value = $value->__toString();
		}
		return $value;
	}

	/**
	 * 设置模描述信息,第一行、第一列
	 * @author baol
	 * @param string $columncount 列数
	 * @param string $description 描述信息
	 */
	private function _setTemplateDes($columncount, $description)
	{
		$excel = $this->_objPHPExcel->getActiveSheet();
		$excel->getRowDimension(1)->setRowHeight($this->_desstyle['row-height']);
		$excel->setCellValue('A1',$description);
		
		for($i=0; $i<$columncount; $i++){
			$style = $excel->getStyle($this->letter[$i].'1');
			$style->getFont()->setBold($this->_desstyle['font-bold']);
			$style->getFont()->setSize($this->_desstyle['font-size']);
			$style->getFont()->getColor()->setRGB($this->_desstyle['font-color']);
			$style->getAlignment()->setHorizontal($this->_desstyle['horizontal']);
			$style->getAlignment()->setVertical($this->_desstyle['vertical']);
			$style->getBorders()->getOutline()->setBorderStyle($this->_desstyle['border-style']);
			$style->getBorders()->getOutline()->getColor()->setRGB($this->_desstyle['border-color']);
			$style->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
			$style->getFill()->getStartColor()->setRGB($this->_desstyle['background-color']);
			$style->getAlignment()->setWrapText(true);
		}
		
		//合并单元格
		$excel->mergeCells('A1:'.$this->letter[$columncount-1].'1');
	}

	
}