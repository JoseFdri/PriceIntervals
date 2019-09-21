<?php

use Models\PriceIntervals;

class PriceIntervalsController {

    public static function insert($request)
    {
        try {
            $data = $request->getBody();
            PriceIntervals::create($data);
            return [
                'status' => 1,
                'message' => 'Price Interval added successfully'
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'message' => $e->getMessage()
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
            $priceInterval = PriceIntervals::where('id', $data['id'])
                ->first();
            $priceInterval->date_start = $data['date_start'];
            $priceInterval->date_end = $data['date_end'];
            $priceInterval->price = $data['price'];
            $priceInterval->save();
            return [
                'status' => 1,
                'message' => 'Price interval updated successfully'
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






