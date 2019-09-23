<?php

use Models\PriceIntervals;
use Libs\Interval;

class PriceIntervalsController {

    public static function insert($request)
    {
        try {
            $data = $request->getBody();
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
            $priceInterval->delete();
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

    public static function get(int $priceIntervalId)
    {
        $priceIntervalId = PriceIntervals::where('id', $priceIntervalId)
                    ->first();
        if($priceIntervalId) {
            return $priceIntervalId;
        }
        return [
            'status' => 0,
            'message' => 'Price Interval not found'
        ];
    }

    public static function reset() {
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
}






