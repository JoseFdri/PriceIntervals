<?php
namespace Libs;

use Illuminate\Database\Capsule\Manager as DB;
use Models\PriceIntervals;

class Interval {
    private $startDate;
    private $endDate;
    private $price;
    private $id;
    private $status = true;
    private $message = 'Successful';

    public function __construct($startDate, $endDate, $price, $id = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->price = $price;
        $this->id = $id;
    }

    private function setStatus($status) {
        $this->status = $status;
    }

    private function setMessage($message) {
        $this->message = $message;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getMessage() {
        return $this->message;
    }

    public function validateDates()
    {
        if(!$this->validateDate($this->startDate)) {
            $this->setStatus(false);
            $this->setMessage('Starting date is not correct');
           return false;
        }
        if(!$this->validateDate($this->endDate)) {
            $this->setStatus(false);
            $this->setMessage('Ending date is not correct');
            return false;
        }
        if(!is_numeric($this->price)) {
            $this->setStatus(false);
            $this->setMessage('Price is not correct');
            return false;
        }
        if($this->startDate > $this->endDate) {
            $this->setStatus(false);
            $this->setMessage('Invalid date range');
            return false;
        }
        return true;
    }
    public function process()
    {
        if(!$this->validateDates()){
            return $this;
        }
        if($this->id) {
            $this->deleteInterval($this->id);
        }
        $this->deleteMiddleInterval();
        $parentInterval = $this->getParentInterval();
        if($parentInterval) {
            if($parentInterval->price != $this->price){
                $this->splitParentInterval([$parentInterval]);
            }else {
                $this->setStatus(false);
                $this->setMessage('There is a Interval that already include this range of time');
            }
        } else {
            $crossStartInterval = $this->getCrossStartDateInterval();
            $crossEndInterval = $this->getCrossEndDateInterval();
            if($crossStartInterval || $crossEndInterval) {
                $this->splitCrossInterval($crossStartInterval, $crossEndInterval);
            } else {
                $newInterval = [
                    'date_start' => $this->startDate,
                    'date_end' => $this->endDate,
                    'price' => $this->price
                ];
                $this->createInterval($newInterval);
            }
        }
        $this->simplifyIntervals();
        return $this;
    }

    function validateDate($date)
    {
        $format = 'Y-m-d';
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    private function simplifyIntervals()
    {
        $intervals = PriceIntervals::orderBy('date_start')->get();
        $newIntervals = [];
        $intervalsToDelete = [];
        for ($i = 0; $i < count($intervals); $i++) {
            $interval = $intervals[$i];
            if(in_array($interval->id, $intervalsToDelete)){
                continue;
            }
            $newInterval = [
                'date_start' => $interval->date_start,
                'date_end' => $interval->date_end,
                'price' => $interval->price
            ];
            $isSimplified = false;
            for ($a = 0; $a < count($intervals); $a++) {
                $subInterval = $intervals[$a];
                if($this->modifyDate($newInterval['date_end'], 1) == $subInterval->date_start
                    && $interval->price == $subInterval->price)
                {
                    $newInterval['date_end'] = $subInterval->date_end;
                    $isSimplified = true;
                    $intervalsToDelete[] = $subInterval->id;
                }
            }
            if($isSimplified) {
                $intervalsToDelete[] = $interval->id;
                $newIntervals[$interval->id] = $newInterval;
            }
        }
        if(count($newIntervals) > 0) {
            PriceIntervals::insert($newIntervals);
        }
        if(count($intervalsToDelete) > 0) {
            PriceIntervals::whereIn('id', $intervalsToDelete)->delete();
        }
    }

    private function splitCrossInterval($crossStartInterval = null, $crossEndInterval  = null){
        $newInterval = [
            'date_start' => '',
            'date_end' => '',
            'price' => 0
        ];
        if($crossStartInterval && $crossEndInterval) {
            if($crossStartInterval->date_end != $crossStartInterval->date_start
                && $crossEndInterval->date_end != $crossEndInterval->date_start
                && $crossStartInterval->date_end == $this->startDate
                && $crossEndInterval->date_end == $this->endDate)
            {
                for ($i = 0; $i < 2; $i++){
                    if($i == 0){
                        $newInterval['date_start'] = $crossStartInterval->date_start;
                        $newInterval['date_end'] = $this->modifyDate($this->startDate, 1, false);
                        $newInterval['price'] = $crossStartInterval->price;
                    } else if ($i == 1) {
                        $newInterval['date_start'] = $this->startDate;
                        $newInterval['date_end'] = $this->endDate;
                        $newInterval['price'] = $this->price;
                    }
                    $this->createInterval($newInterval);
                }
                $this->deleteInterval($crossStartInterval->id);
                $this->deleteInterval($crossEndInterval->id);
            } else if($crossStartInterval->date_start == $this->startDate && $this->endDate == $crossEndInterval->date_start){
                for ($i = 0; $i < 2; $i++){
                    if($i == 0){
                        $newInterval['date_start'] = $this->startDate;
                        $newInterval['date_end'] = $this->endDate;
                        $newInterval['price'] = $this->price;
                    } else if ($i == 1) {
                        $newInterval['date_start'] = $crossEndInterval->date_end;
                        $newInterval['date_end'] = $this->modifyDate($this->endDate, 1);
                        $newInterval['price'] = $crossEndInterval->price;
                    }
                    $this->createInterval($newInterval);
                }
                $this->deleteInterval($crossStartInterval->id);
                $this->deleteInterval($crossEndInterval->id);
            }else if ($this->startDate == $crossStartInterval->date_end && $this->endDate == $crossEndInterval->date_start) {
                for ($i = 0; $i < 3; $i++){
                    if($i == 0){
                        $newInterval['date_start'] = $crossStartInterval->date_start;
                        $newInterval['date_end'] = $this->modifyDate($this->startDate, 1, false);
                        $newInterval['price'] = $crossStartInterval->price;
                    } else if ($i == 1) {
                        $newInterval['date_start'] = $this->startDate;
                        $newInterval['date_end'] = $this->endDate;
                        $newInterval['price'] = $this->price;
                    }else if ($i == 2) {
                        $newInterval['date_start'] =  $this->modifyDate($this->endDate, 1);
                        $newInterval['date_end'] = $crossEndInterval->date_end;
                        $newInterval['price'] = $crossEndInterval->price;
                    }
                    $this->createInterval($newInterval);
                }
                $this->deleteInterval($crossStartInterval->id);
                $this->deleteInterval($crossEndInterval->id);
            } else if ($this->startDate == $crossStartInterval->date_start && $this->endDate == $crossEndInterval->date_end) {
                $newInterval['date_start'] =  $this->startDate;
                $newInterval['date_end'] = $this->endDate;
                $newInterval['price'] = $this->price;
                $this->deleteInterval($crossStartInterval->id);
                $this->deleteInterval($crossEndInterval->id);
                $this->createInterval($newInterval);
            }
        } else {
            if($crossStartInterval) {
                for ($i = 0; $i < 2; $i++){
                    if($i == 0){
                        if($crossStartInterval->date_start == $crossStartInterval->date_end){
                           continue;
                        }
                        $newInterval['date_start'] = $crossStartInterval->date_start;
                        $newInterval['date_end'] = $this->modifyDate($this->startDate, 1, false);
                        $newInterval['price'] = $crossStartInterval->price;
                    } else if ($i == 1) {
                        $newInterval['date_start'] = $this->startDate;
                        $newInterval['date_end'] = $crossEndInterval ? $crossStartInterval->date_end : $this->endDate;
                        $newInterval['price'] = $this->price;
                    }
                    $this->createInterval($newInterval);
                }
                $this->deleteInterval($crossStartInterval->id);
            }
            if($crossEndInterval) {
                for ($i = 0; $i < 2; $i++){
                    if($i == 0){
                        $newInterval['date_start'] = $crossStartInterval ? $crossEndInterval->date_start : $this->startDate;
                        $newInterval['date_end'] = $this->endDate;
                        $newInterval['price'] = $this->price;
                    } else if ($i == 1) {
                        $newInterval['date_start'] = $this->modifyDate($this->endDate, 1);
                        $newInterval['date_end'] = $crossEndInterval->date_end;
                        $newInterval['price'] = $crossEndInterval->price;
                    }
                    $this->createInterval($newInterval);
                }
                $this->deleteInterval($crossEndInterval->id);
            }
        }
    }

    private function modifyDate($date, $days, $add = true)
    {
        $operation = ' + ';
        if(!$add) {
            $operation = ' - ';
        }
        return  date('Y-m-d', strtotime($date.$operation.$days.' days'));
    }

    private function splitParentInterval($intervals){
        $newInterval = [
            'date_start' => '',
            'date_end' => '',
            'price' => 0
        ];
        if(count($intervals) == 1){
            $parentInterval = $intervals[0];
            if($parentInterval->date_start != $this->startDate && $parentInterval->date_end != $this->endDate){
                for ($i = 0; $i < 3; $i++){
                    if($i == 0){
                        $newInterval['date_start'] = $parentInterval->date_start;
                        $newInterval['date_end'] = $this->modifyDate($this->startDate, 1, false);
                        $newInterval['price'] = $parentInterval->price;
                    }else if ($i == 1) {
                        $newInterval['date_start'] = $this->startDate;
                        $newInterval['date_end'] = $this->endDate;
                        $newInterval['price'] = $this->price;
                    }else if ($i == 2) {
                        $newInterval['date_start'] = $this->modifyDate($this->endDate, 1);
                        $newInterval['date_end'] = $parentInterval->date_end;
                        $newInterval['price'] = $parentInterval->price;
                    }
                    $this->createInterval($newInterval);
                }
            }else if ($parentInterval->date_start == $this->startDate && $parentInterval->date_end != $this->endDate) {
                for ($i = 0; $i < 2; $i++){
                    if($i == 0){
                        $newInterval['date_start'] = $this->startDate;
                        $newInterval['date_end'] = $this->endDate;
                        $newInterval['price'] = $this->price;
                    }else if ($i == 1) {
                        $newInterval['date_start'] = $this->modifyDate($this->endDate, 1);
                        $newInterval['date_end'] = $parentInterval->date_end;
                        $newInterval['price'] = $parentInterval->price;
                    }
                    $this->createInterval($newInterval);
                }
            }else if ($parentInterval->date_end == $this->endDate && $parentInterval->date_start != $this->startDate) {
                for ($i = 0; $i < 2; $i++){
                    if($i == 0){
                        $newInterval['date_start'] = $parentInterval->date_start;
                        $newInterval['date_end'] = $this->modifyDate($this->startDate, 1, false);
                        $newInterval['price'] = $parentInterval->price;
                    }else if ($i == 1) {
                        $newInterval['date_start'] = $this->startDate;
                        $newInterval['date_end'] = $parentInterval->date_end;
                        $newInterval['price'] = $this->price;
                    }
                   $this->createInterval($newInterval);
                }
            }else if ($parentInterval->date_end == $this->endDate && $parentInterval->date_start == $this->startDate) {
                $this->createInterval($newInterval);
            }
            $this->deleteInterval($parentInterval->id);
        }
    }

    private function createInterval($data)
    {
        PriceIntervals::create($data);
    }

    private function deleteInterval($id)
    {
        PriceIntervals::where('id', $id)->first()->delete();
    }
    private function getParentInterval()
    {
        return DB::table('price_intervals')
                    ->selectRaw('*')
                    ->where('date_start','<=', $this->startDate)
                    ->where('date_end','>=', $this->endDate)
                    ->first();
    }

    private function deleteMiddleInterval() {
        return DB::table('price_intervals')
            ->selectRaw('*')
            ->where('date_start','>=', $this->startDate)
            ->where('date_end','<=', $this->endDate)
            ->delete();
    }

    private function getCrossStartDateInterval()
    {
        return DB::table('price_intervals')
            ->selectRaw('*')
            ->where('date_start','<=', $this->startDate)
            ->where('date_end','>=', $this->startDate)
            ->first();
    }

    private function getCrossEndDateInterval()
    {
        return DB::table('price_intervals')
            ->selectRaw('*')
            ->where('date_start','<=', $this->endDate)
            ->where('date_end','>=', $this->endDate)
            ->first();
    }
}