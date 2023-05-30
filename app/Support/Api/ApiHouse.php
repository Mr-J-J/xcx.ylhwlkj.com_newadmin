<?php
namespace App\Support\Api;

class ApiHouse
{
    static $driverClass = '\\App\\Support\\MApi';

    /**
     * 释放锁座
     *
     * @param [type] $order_id
     * @return void
     */
    static function unLockSeat($order_id){
        $return = \App\Support\MApi::unLockSeat($order_id);
        return $return;
    }


    /**
     * 刷新锁座
     *
     * @param [type] $order_id
     * @return void
     */
    static function refreshSeat($order_id){
        $return = \App\Support\MApi::refreshSeat($order_id);
        return $return;
    }

    /**
     * 确认购票
     *
     * @param [type] $order_id
     * @return void
     */
    static function payOrder($order_id,$channel_order_id){
        $return = \App\Support\MApi::payOrder($order_id,$channel_order_id);
        return $return;
    }


    /**
     * 锁座
     *
     *
     * @param [type] $param
     * @return void
     */
    static function lockSeat($param){
//        logger($param);
        // 聚福宝锁座返回
        // "order_id": "506614354417282241", //聚福宝订单号
        // "count": 2,
        // "placed_time": 1658893474, //下单时间（时间戳例：1522287039）
        // "expire_time": "1658894054", //锁座有效截止时间（时间戳例：1522287039）
        // "film_id": 1308,
        // "cinema_id": 60504,
        // "hall_name": "6厅（需自备3D眼镜）",
        // "show_version": "英语 3D",
        // "price": "9600",
        // "show_time": 1658899800
        extract($param);
        $result = \App\Support\MApi::seatLock($account_id,$seat_names,$paiqi_id,$seat_ids,$phone_num,$seat_areas);
        $return = array(
            'ErrNo'=>0,
            'Data' =>$result
        );
        if(empty($result)){
            $return['ErrNo'] = 1001;
            $return['Msg'] = '_jfb';
            return $return;
        }
        if(!empty($return['Data'])){
            $resultData= $return['Data'];
            $resultData['SID'] = $resultData['order_id'];
            $return['Data'][] = $resultData;
        }
        return $return;
    }

    /**
     * 获取座位图
     *
     * @param [type] $show_index
     * @param [type] $cinema_id
     * @return void
     */
    static function getSeatByShowIndex($show_index,$cinema_id){
        //聚福宝座位图
        $seatList = \App\Support\MApi::seatList($show_index);
        return $seatList;
    }

    /**
     * 聚福宝座位图
     *
     * @param [type] $show_index
     * @param [type] $cinema_id
     * @return void
     */
    static function formatSeatsJufubao($show_index,$cinema_id){
//        logger($show_index);
        $return = array();
        $seatsListData = \App\Support\MApi::seatList($show_index);
//        logger($seatsListData);
        $seatsList = $seatsListData['seats']??[];
        $return['seat_count'] = count($seatsList);
        // $columnsArr = array_unique(array_column($seatsList,'column'));
        // $rowsArr = array_unique(array_column($seatsList,'row'));
        // sort($rowsArr);
        // sort($columnsArr);
        // $rowStep = $colStep = 0;
        // if($seatsList){
        //     $rowStep = abs($rowsArr[0] - $rowsArr[1]);
        //     $colStep = abs($columnsArr[0] - $columnsArr[1]);
        // }

        foreach($seatsList as &$item){
            $item['api_seat'] = $item;
            // $item['id'] = $item['id'];
            // $seatName = explode(':',$item['Name']);
            $row = (int) $item['row'] + 1 + 40;
            $column = (int) $item['column'] + 1 + 16;
            // $item['name'] = "{$row}排{$column}座";
            // $row_letter_pos = strpos($letter , $row);
            // if($row_letter_pos !== false){
            //     $row = $row_letter_pos;
            // }
            $item['SectionID'] = $item['area'];
            $item['top_px'] = (int) $item['row'] + 1;
            $item['left_px'] = (int) $item['column'] + 1;

            $item['RowID'] = $row;
            $item['ColumnID'] = $column;
            // $item['status'] = (($item['status'] == 'Y')? 1: 0); //座位状态:1：可售，0：不可售，-1：删除(非法)

            // $item['flag'] = $item['LoveFlag']; //情侣座标识 0：普通座位 1：情侣座首  座位标记 2：情侣座第二座位标记
            // unset($item['SeatIndex'],$item['Status'],$item['LoveFlag'],$item['Name']);

        }


        $return['seats'] = $seatsList;

        $return['max_column'] = $seatsListData['max_column']??0;
        $return['min_column'] = $seatsListData['min_column']??0;
        $return['max_row'] = $seatsListData['max_row']??0;
        $return['min_row'] = $seatsListData['min_row']??0;
        $return['a']=$seatsListData;
        return $return;
    }
    /**
     * 网票网座位图
     *
     * @return void
     */
    static function formatSeatsWangpiao($show_index,$cinema_id)
    {
        $return = array();
        $seatsList = (array)\App\Support\WpApi::getSeatByShowIndex($show_index,$cinema_id);
        $sellSeatsList = (array) \App\Support\WpApi::getSellSeatInfo($show_index,$cinema_id);
        $sellSeatArr = array();
        if(!empty($sellSeatsList)){
            $sellSeatArr = array_column($sellSeatsList,'SeatID');
        }
        $letter = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $return['seat_count'] = count($seatsList);
        $columnsArr = array_unique(array_column($seatsList,'ColumnID'));
        $rowsArr = array_unique(array_column($seatsList,'RowID'));
        sort($rowsArr);
        sort($columnsArr);
        $rowStep = $colStep = 0;
        if($seatsList){
            $rowStep = abs($rowsArr[0] - $rowsArr[1]);
            $colStep = abs($columnsArr[0] - $columnsArr[1]);
        }

        foreach($seatsList as &$item){
            $item['api_seat'] = $item;
            $item['id'] = $item['SeatIndex'];
            $seatName = explode(':',$item['Name']);
            $row = $seatName[0];
            $column = (int) $seatName[1];
            $item['name'] = "{$row}排{$column}座";
            $row_letter_pos = strpos($letter , $row);
            if($row_letter_pos !== false){
                $row = $row_letter_pos;
            }
            $item['top_px'] = (int)($item['RowID'] / $rowStep) + 1;
            $item['left_px'] = (int)($item['ColumnID'] / $colStep);

            $item['RowID'] = $row;
            $item['ColumnID'] = $column;
            $item['status'] = ($item['Status'] == 'Y')? 1: 0;
            if(in_array($item['SeatID'],$sellSeatArr)){
                $item['status'] = 0;
            }
            $item['flag'] = $item['LoveFlag']; //情侣座标识 0：普通座位 1：情侣座首  座位标记 2：情侣座第二座位标记

            unset($item['SeatIndex'],$item['Status'],$item['LoveFlag'],$item['Name']);

        }


        $return['seats'] = $seatsList;

        $return['max_column'] = empty($columnsArr) ? 0 : max($columnsArr);
        $return['min_column'] = 0;
        $return['max_row'] = empty($rowsArr) ? 0 : max($rowsArr);
        $return['min_row'] = 0;
        return $return;
    }

    /**
     * 影院排期数据
     * 聚福宝-  影院 60504
     *
     * @return void
     */
    static function getFilmShowByDate($cinema_id,$date = 0,$film_id = '')
    {
        $nextDate = strtotime(date('Y-m-d',strtotime('+1 day',$date)));
        $last_key = '';
        $list = [];
        do{
            $apiResult = \App\Support\MApi::filmPaiqiList($cinema_id,$film_id,$last_key);
//            logger($apiResult);
            $data = $apiResult['data']??[];
            $last_key = $apiResult['last_key']??null;
            if(empty($data)){
                break;
            }
            $scheduleList = \App\ApiModels\Wangpiao\Schedules::syncData($data,$film_id);

            $data = array_filter($scheduleList,function($v) use ($film_id,$date,$nextDate){
                return ($v->film_id == $film_id && $v->show_time > $date && $v->show_time < $nextDate);
            });
            $list = array_merge($list,$data);

        }while(!empty($last_key));

        return $list;
    }

    /**
     * 影院下的电影
     *
     * @param [type] $cinema_id
     * @return void
     */
    static function getFilmList($cinema_id)
    {
        $apiResult = \App\Support\MApi::filmList($cinema_id);
        \App\ApiModels\Wangpiao\Film::syncData($apiResult,2);
        return $apiResult;
    }
    /**
     * getCityList() 获取城市列表
     *
     * @param [type] $name
     * @param [type] $arguments
     * @return void
     */
    static function __callStatic($name, $arguments)
    {
        return self::$driverClass::$name(...$arguments);
    }
}
