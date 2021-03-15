<?php

use League\CLImate\CLImate;
/** 引用CLImate元件 */
require("../vendor/autoload.php");
/** 
 * 讀取口罩資料，並以CLImate元件格式輸出至命令列
 */
class Mask
{
    /** @var array $maskDatas 用來讀取口罩資料來源 */
    protected $maskDatas;
    /** @var array $streetArray 用來儲存過濾地址後的藥局資料 */
    public $streetArray=[];
    /** @var CLImate $climate CLImate物件 */
    protected $climate;
    /** @var array $pharmacyInformations 用來儲存最後要輸出的藥局資料 */
    public $pharmacyInformations=[];

    /** 
     * Mask 類別的建構子，使讀取的藥局資料轉換格式
     * 以及初始化CLImate元件
     * 
     * @return void
     */
    function __construct()
    {
        $this->dataFormatModify();
        $this->climate = new CLImate();
    }

    /**
     * 使用健保署API讀取口罩照資料
     * 轉換成array格式
     * 
     * @return void
     */
    private function dataFormatModify ()
    {
        $this->maskDatas = file_get_contents("https://data.nhi.gov.tw/Datasets/Download.ashx?rid=A21030000I-D50001-001&l=https://data.nhi.gov.tw/resource/mask/maskdata.csv");
        $this->maskDatas = explode("\n", $this->maskDatas);
        for ($i = 0; $i < count($this->maskDatas); $i++)
        {
            $this->maskDatas[$i] = explode(',', $this->maskDatas[$i]);
            $len = count($this->maskDatas[$i]);
            for ($j = 0; $j < $len; $j++)
            {
                if($j != 1 && $j != 2 && $j != 4)
                {
                    unset($this->maskDatas[$i][$j]);
                }
            }
            $this->maskDatas[$i] = array_values($this->maskDatas[$i]);
        }
    }

    /**
     * 依照使用者給予的資訊
     * 過濾藥局資料
     * 並依口罩數量多寡排序
     * 
     * @param string $streetString 是使用者要查詢的地址字串
     * 
     * @return void
     */
    private function streetFliter ($streetString)
    {
        foreach ($this->maskDatas as $key => $pharmacyInformations)
        {
            foreach ($pharmacyInformations as $value)
            {
                if(strstr($value, ($streetString !== null ? $streetString : '藥局')) !== false)
                { 
                    array_push($this->streetArray, $key);
                } 
            } 
        }
        array_push($this->pharmacyInformations, $this->maskDatas[0]);
        foreach ($this->streetArray as $key => $value)
        {
            if(array_key_exists($value,$this->maskDatas))
            {
                array_push($this->pharmacyInformations, $this->maskDatas[$value]);
            }
        }
        $this->sort();
    }

    /**
     * 依照使用者所要查詢的地址字串，使用函數過濾藥局資訊
     * 並以CLImate元件格式輸出資料至命令列
     * 
     * @param string $pharmacy 是使用者要查詢的地址字串
     * 
     * @return void
     */
    public function toClimateFormat($pharmacy)
    {
        $this->streetFliter($pharmacy);
        $this->climate->table($this->pharmacyInformations);   
    }

    /**
     * 以口罩多到少排序藥局資訊
     * 
     * @return void
     */
    private function sort()
    {
        for($i = 0; $i < count($this->pharmacyInformations); $i++)
        {
            for($j = $i + 1; $j < count($this->pharmacyInformations); $j++)
            {
                if($this->pharmacyInformations[$i][2] < $this->pharmacyInformations[$j][2])
                {
                    $tmp = $this->pharmacyInformations[$i];
                    $this->pharmacyInformations[$i] = $this->pharmacyInformations[$j];
                    $this->pharmacyInformations[$j] = $tmp;
                }
            }
        }
    }
}

/** 實例化Mask類別 */
$mask = new Mask();
$mask->toClimateFormat(isset($argv[1]) ? $argv[1] : null);