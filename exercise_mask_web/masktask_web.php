<?php
        use League\CLImate\CLImate;

        require("../vendor/autoload.php");
        header("Content-type:text/html;charset=utf-8");
        class Mask
        {
            protected $maskDatas;
            public $streetArray=[];
            protected $climate;
            public $pharmacyInformations=[];
        
            function __construct()
            {
                $this->dataFormatModify();
                $this->climate = new CLImate();
            }
        
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
        
            public function streetFliter ($streetString)
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
        
            public function toClimateFormat($pharmacy)
            {
                $this->streetFliter($pharmacy);
                $this->climate->table($this->pharmacyInformations);   
            }
        
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

        $streetname = $_POST["streetname"];
        $mask = new Mask();
        $mask->streetFliter($streetname);
        $json = $mask->pharmacyInformations;
        $date = json_encode($json);  //編譯陣列轉化為json資料
	    echo $date;  //將json資料傳回網頁
        /*function toTableFormat($n)
        {
            $mask = new Mask();
            $mask->streetFliter($n);
            echo "<table id='t01'>
            <tr>";
            foreach($mask->pharmacyInformations[0] as $title)
            {
                echo "<th>".$title."</th>";
            }
            echo "</tr>";

            for($i = 1; $i < count($mask->pharmacyInformations); $i++)
            {
                echo "<tr>";
                foreach($mask->pharmacyInformations[$i] as $context)
                {
                    echo "<td>".$context."</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }*/
        ?>