<?php

use Models\PriceIntervals;
use Libs\Interval;

class PriceIntervalsController {

    public static function insert($request)
    {
        try {
            $data = $request->getBody();
            if(!self::validateDates(['date_start', 'date_end', 'price'])){
                header("HTTP/1.1 400 Bad Request");
                exit();
            }
            $interval = new Interval($data['date_start'], $data['date_end'], $data['price']);
            $process = $interval->process();
            return [
                'status' => $process->getStatus(),
                'message' => $process->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'message' => $e->getMessage() . '| File: '.$e->getFile().'| line: '. $e->getLine()
            ];
        }
    }

    public static function index()
    {
        $html = file_get_contents('views/index.php');
        return $html;
    }

    public static function update($request)
    {
        try {
            $data = $request->getBody();
            if(!self::validateDates(['date_start', 'date_end', 'price', 'id'])){
                header("HTTP/1.1 400 Bad Request");
                exit();
            }
            $interval = new Interval($data['date_start'], $data['date_end'], $data['price'], $data['id']);
            $process = $interval->process();
            return [
                'status' => $process->getStatus(),
                'message' => $process->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'message' => $e->getMessage()
            ];
        }

    }

    public static function delete(int $priceIntervalId)
    {
        try {
            $priceInterval = PriceIntervals::where('id', $priceIntervalId)
                ->first();
            if($priceInterval) {
                $priceInterval->delete();
            }
            return [
                'status' => 1,
                'message' => 'Price interval deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function all()
    {
        return PriceIntervals::orderBy('date_start')->get();
    }

    public static function reset()
    {
        try {
            PriceIntervals::truncate();
            return [
                'status' => 1,
                'message' => 'Price intervals reset successfully'
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    private static function validateDates($requireFields){
        foreach($requireFields as $requireField) {
            if(!array_key_exists($requireField, $requireFields)){
                return false;
            }
        }
        return true;
    }
}






