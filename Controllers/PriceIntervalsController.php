<?php

use Models\PriceIntervals;
use Libs\Interval;

class PriceIntervalsController {

    /**
     * Create a new interval
     *
     * @param $request
     *
     * @return array;
     */
    public static function insert($request)
    {
        try {
            $data = $request->getBody();
            if(!self::validateDates(['date_start', 'date_end', 'price'], $data)){
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

    /**
     * Return the homepage
     *
     * @return string;
     */
    public static function index()
    {
        $html = file_get_contents('views/index.php');
        return $html;
    }

    /**
     * Update an interval
     *
     * @param $request
     *
     * @return array;
     */
    public static function update($request)
    {
        try {
            $data = $request->getBody();
            if(!self::validateDates(['date_start', 'date_end', 'price', 'id'], $data)){
                header("HTTP/1.1 400 Bad Request");
                exit();
            }
            $priceInterval = PriceIntervals::where('id', $data['id'])
                ->first();
            if($priceInterval) {
                $interval = new Interval($data['date_start'], $data['date_end'], $data['price'], $data['id']);
                $process = $interval->process();
                return [
                    'status' => $process->getStatus(),
                    'message' => $process->getMessage()
                ];
            } else {
                header("HTTP/1.1 400 Bad Request");
                exit();
            }
        } catch (Exception $e) {
            return [
                'status' => 0,
                'message' => $e->getMessage()
            ];
        }

    }

    /**
     * Delete a specific interval
     *
     * @param $priceIntervalId
     *
     * @return array;
     */
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

    /**
     * Get all the intervals
     *
     *
     * @return array;
     */
    public static function all()
    {
        return PriceIntervals::orderBy('date_start')->get();
    }

    /**
     * Truncate the price_intervals table in order to start from the scratch
     *
     * @return array;
     */
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

    /**
     * Verify that require fields are present on the request
     *
     * @param $requireFields
     * @param $data
     *
     * @return bool;
     */
    private static function validateDates($requireFields, $data){
        foreach($requireFields as $requireField) {
            if(!array_key_exists($requireField, $data)){
                return false;
            }
        }
        return true;
    }
}






