<?php
 /**
  * DateTimeHelper.php
  *
  * @package yunke\helpers
  * @example
   <code>
     DateTimeHelper::addDate('2012-12-01',1,'y');
     DateTimeHelper::getWeekDay('2012/10/01','/');
     DateTimeHelper::isLeapYear('2012');
     DateTimeHelper::timeFromNow(strtotime("2012-10-26 14:15:13"));
    </code>
  * @author        xiongzg <xiongzg@mysoft.com.cn>
  * @create_time	   2015-03-18 11:15
  */

namespace yunke\helpers;

class DateTimeHelper
{
    /**
     * Checks for leap year, returns true if it is. No 2-digit year check. Also
     * handles julian calendar correctly.
     * @param integer $year year to check
     * @return boolean true if is leap year
     */
    public static function isLeapYear($year)
    {
        $year = self::digitCheck($year);
        if ($year % 4 != 0)
            return false;

        if ($year % 400 == 0)
            return true;
        // if gregorian calendar (>1582), century not-divisible by 400 is not leap
        else if ($year > 1582 && $year % 100 == 0)
            return false;
        return true;
    }

    /**
     * Fix 2-digit years. Works for any century.
     * Assumes that if 2-digit is more than 30 years in future, then previous century.
     * @param integer $y year
     * @return integer change two digit year into multiple digits
     */
    protected static function digitCheck($y)
    {
        if ($y < 100){
            $yr = (integer) date("Y");
            $century = (integer) ($yr /100);

            if ($yr%100 > 50) {
                $c1 = $century + 1;
                $c0 = $century;
            } else {
                $c1 = $century;
                $c0 = $century - 1;
            }
            $c1 *= 100;
            // if 2-digit year is less than 30 years in future, set it to this century
            // otherwise if more than 30 years in future, then we set 2-digit year to the prev century.
            if (($y + $c1) < $yr+30) $y = $y + $c1;
            else $y = $y + $c0*100;
        }
        return $y;
    }

    /**
     * Returns 4-digit representation of the year.
     * @param integer $y year
     * @return integer 4-digit representation of the year
     */
    public static function get4DigitYear($y)
    {
        return self::digitCheck($y);
    }

    /**
     * Checks to see if the year, month, day are valid combination.
     * @param integer $y year
     * @param integer $m month
     * @param integer $d day
     * @return boolean true if valid date, semantic check only.
     */
    public static function isValidDate($y,$m,$d)
    {
        return checkdate($m, $d, $y);
    }

    public static function checkDate($date, $separator = "-") { //检查日期是否合法日期
        $dateArr = explode ($separator, $date);
        return self::isValidDate ($dateArr[0], $dateArr[1], $dateArr[2]);
    }

    /**
     * Checks to see if the hour, minute and second are valid.
     * @param integer $h hour
     * @param integer $m minute
     * @param integer $s second
     * @param boolean $hs24 whether the hours should be 0 through 23 (default) or 1 through 12.
     * @return boolean true if valid date, semantic check only.
     * @since 1.0.5
     */
    public static function isValidTime($h,$m,$s,$hs24=true)
    {
        if($hs24 && ($h < 0 || $h > 23) || !$hs24 && ($h < 1 || $h > 12)) return false;
        if($m > 59 || $m < 0) return false;
        if($s > 59 || $s < 0) return false;
        return true;
    }

    //检查时间是否合法时间
    public static function checkTime($time, $separator = ":")
    {
        $timeArr = explode($separator, $time);
        return self::isValidTime($timeArr[0], $timeArr[1],$timeArr[2]);
    }

    //日期的增加
    public static function addDate($date, $int, $unit = "d")
    {
        $value = array('y'=>'', 'm'=>'', 'd'=>'');
        $dateArr = explode ( "-", $date);
        if(array_key_exists($unit, $value)){
            $value[$unit] = $int;
        }else{
            return false;
        }
        return date ("Y-m-d", mktime (0, 0, 0, $dateArr[1] + $value['m'], $dateArr[2] + $value['d'], $dateArr[0] +$value['y']));
    }

    //日期的增加
    public static function addDateTime($date, $int, $unit = "d")
    {
        $value = array('y'=>'', 'm'=>'', 'd'=>'', 'h'=>'', 'i'=>'');
        $dateArr = preg_split ( "/-|\s|:/", $date);
        if(array_key_exists($unit, $value)){
            $value[$unit] = $int;
        }else{
            return false;
        }
        return date ("Y-m-d H:i:s", mktime($dateArr[3]+ $value['h'], $dateArr[4]+ $value['i'], $dateArr[5], $dateArr[1] + $value['m'], $dateArr[2] + $value['d'], $dateArr[0] + $value['y']));
    }

    //取当前时间后几天，天数增加单位为1
    public static function addDayTimestamp($ntime, $aday)
    {
        $dayst = 3600 * 24;
        $oktime = $ntime + ($aday * $dayst);
        return $oktime;
    }

    //时间比较函数，返回两个日期相差几秒、几分钟、几小时或几天
    public static function dateDiff($begin, $end, $unit = "d")
    {
        $diff = strtotime($end) - strtotime($begin);
        switch($unit)
        {
            case "y": $retval = bcdiv($diff, (60 * 60 * 24 * 365)); break;
            case "m": $retval = bcdiv($diff, (60 * 60 * 24 * 30)); break;
            case "w": $retval = bcdiv($diff, (60 * 60 * 24 * 7)); break;
            case "d": $retval = bcdiv($diff, (60 * 60 * 24)); break;
            case "h": $retval = bcdiv($diff, (60 * 60)); break;
            case "i": $retval = bcdiv($diff, 60); break;
            case "s": $retval = $diff; break;
        }
        return $retval;
    }

    //计算出给出的日期是星期几
    public static function getWeekDay($date, $separator = "-")
    {
        $dateArr = explode ($separator, $date);
        return date ("w", mktime ( 0, 0, 0, $dateArr[1], $dateArr[2], $dateArr[0]));
    }

    //让日期显示为:XX天XX年以前
    public static function timeFromNow($dateline)
    {
        if(empty($dateline)) return false;
        $seconds = time() - $dateline;
        if($seconds < 60){
            return "1分钟前";
        }elseif($seconds < 3600){
            return floor($seconds/60)."分钟前";
        }elseif($seconds  < 24*3600){
            return floor($seconds/3600)."小时前";
        }elseif($seconds < 48*3600){
            return date("昨天 H:i", $dateline)."";
        }else{
            return date('Y-m-d', $dateline);
        }
    }

    public static function transDateToChs($date)
    {
        if (empty ($date)) return '今日';
        date_default_timezone_set('PRC');
        $dates = date ('Y年m月d日', strtotime ($date));
        return $dates;
    }

    // 08/31/2004 => 2004-08-31
    public static function TransDateUI($datestr, $type = 'Y-m-d')
    {
        if ($datestr == Null)
            return Null;
        $target = $datestr;
        $arr_date = preg_split ( "/\//", $target);
        $monthstr = $arr_date[0];
        $daystr = $arr_date[1];
        $yearstr = $arr_date[2];
        $result = date ($type, mktime (0, 0, 0, $monthstr, $daystr, $yearstr));
        return $result;
    }

    // 12/20/2004 10:55 AM => 2004-12-20 10:55:00
    public static function TransDateTimeUI($datestr, $type = 'Y-m-d H:i:s')
    {
        if ($datestr == Null)
            return Null;
        $target = $datestr;
        $arr_date = preg_split ( "/\/|\s|:/", $target);
        $monthstr = $arr_date[0];
        $daystr = $arr_date[1];
        $yearstr = $arr_date[2];
        $hourstr = $arr_date[3];
        $minutesstr = $arr_date[4];
        $result = date ($type, mktime ($hourstr, $minutesstr, 0, $monthstr, $daystr, $yearstr));
        return $result;
    }

    /**
     * 格式化日期，默认返回格式 2015-03-01 00:00:00
     * @param string $time 日期|时间
     * @param string $format 格式
     * @return string
     */
    public static function FormatDate($time,$format = 'Y-m-d H:i:s'){
        if($time == '' || $time == '0000-00-00 00:00:00')
            return '';
        $is_date = strtotime($time)?strtotime($time):false;
        if(!$is_date)
            return '';
        return date($format,$is_date);
    }
} 