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

    /**
     * Initialize this Class
     *
     * @param $startDate
     * @param $endDate
     * @param $price
     * @param $id
     *
     * @return void;
     */
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

    /**
     * Validate Interval properties
     *
     * @return bool;
     */
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

    /**
     * Start procesing the new interval
     *
     * @return $this;
     */
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
                $this->splitParentInterval($parentInterval);
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
                $this->createInterval($this->startDate, $this->endDate, $this->price);
            }
        }
        $this->simplifyIntervals();
        return $this;
    }

    /**
     * Verify if a date is in the correct format
     *
     * @param $date
     *
     * @return string;
     */
    function validateDate($date)
    {
        $format = 'Y-m-d';
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Simplify intervals that are consecutive
     *
     * @return void;
     */
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

    /**
     * Split a interval that contains the start date of a new interval
     *
     * @param $startInterval
     * @param $closeInterval
     *
     * @return void;
     */
    private function splitCrossStartInterval($startInterval, $closeInterval = false) {
        $endDate = $closeInterval ? $this->endDate : $startInterval->date_end;
        if($startInterval->date_start < $this->startDate && $startInterval->date_end > $this->startDate) {
            $this->createInterval($startInterval->date_start, $this->modifyDate($this->startDate, 1, false), $startInterval->price);
            $this->createInterval($this->startDate, $endDate, $this->price);
        } else if ($startInterval->date_start < $this->startDate && $this->startDate == $startInterval->date_end) {
            $this->createInterval($startInterval->date_start, $this->modifyDate($this->startDate, 1, false), $startInterval->price);
            $this->createInterval($this->startDate, $endDate, $this->price);
        } else if ($startInterval->date_start == $this->startDate && $startInterval->date_end > $this->startDate) {
            $this->createInterval($this->startDate, $endDate, $this->price);
        }else if ($startInterval->date_start == $startInterval->date_end) {
            $this->createInterval($this->startDate, $endDate, $this->price);
        }
        $this->deleteInterval($startInterval->id);
    }

    /**
     * Split a interval that contains the end date of a new interval
     *
     * @param $endInterval
     * @param $closeInterval
     *
     * @return void;
     */
    private function splitCrossEndInterval($endInterval, $closeInterval = false)
    {
        $startDate = $closeInterval ? $this->startDate : $endInterval->date_start;
        if($endInterval->date_start < $this->endDate && $this->endDate < $endInterval->date_end) {
            $this->createInterval($startDate, $this->endDate, $this->price);
            $this->createInterval($this->modifyDate($this->endDate, 1), $endInterval->date_end, $endInterval->price);
        }else if ($endInterval->date_start < $this->endDate && $this->endDate == $endInterval->date_end) {
            $this->createInterval($startDate, $this->endDate, $this->price);
        }else if ($endInterval->date_start == $this->endDate && $this->endDate < $endInterval->date_end) {
            $this->createInterval($startDate, $this->endDate, $this->price);
            $this->createInterval($this->modifyDate($this->endDate, 1), $endInterval->date_end, $endInterval->price);
        }else if ($endInterval->date_start == $endInterval->date_end) {
            $this->createInterval($startDate, $this->endDate, $this->price);
        }
        $this->deleteInterval($endInterval->id);
    }

    /**
     * Create a middle interval in order to fill a space between intervals when is required
     *
     * @param $startInterval
     * @param $endInterval
     *
     * @return void;
     */
    private function createMiddleInterval($startInterval, $endInterval)
    {
        $consecutive = $this->modifyDate($startInterval->date_end, 1);
        if($consecutive != $endInterval->date_start){
            $this->createInterval($consecutive, $this->modifyDate($endInterval->date_start, 1, false), $this->price);
        }
    }

    /**
     * Start splitting intervals that contains the start date or end date of a new interval
     *
     * @param $startInterval
     * @param $endInterval
     *
     * @return void;
     */
    private function splitCrossInterval($startInterval = null, $endInterval  = null){
        if ($startInterval && !$endInterval) {
            $this->splitCrossStartInterval($startInterval, true);
        }else if($endInterval && !$startInterval) {
            $this->splitCrossEndInterval($endInterval, true);
        }else if ($endInterval && $startInterval) {
            $this->splitCrossStartInterval($startInterval);
            $this->createMiddleInterval($startInterval, $endInterval);
            $this->splitCrossEndInterval($endInterval);
        }
    }

    /**
     * Add or rest days to a specific date
     *
     * @param $date
     * @param $days
     * @param $add
     *
     * @return string;
     */
    private function modifyDate($date, $days, $add = true)
    {
        $operation = $add ? ' + ' : ' - ';
        return  date('Y-m-d', strtotime($date.$operation.$days.' days'));
    }

    /**
     * If a new interval is contain in another interval, this split the parent interval
     *
     * @param $parent
     *
     * @return void;
     */
    private function splitParentInterval($parent)
    {
        if($parent->date_start < $this->startDate && $this->endDate < $parent->date_end) {
            $this->createInterval($parent->date_start, $this->modifyDate($this->startDate, 1, false), $parent->price);
            $this->createInterval($this->startDate, $this->endDate, $this->price);
            $this->createInterval($this->modifyDate($this->endDate, 1), $parent->date_end, $parent->price);
        } else if ($parent->date_start == $this->startDate && $this->endDate < $parent->date_end) {
            $this->createInterval($this->startDate, $this->endDate, $this->price);
            $this->createInterval($this->modifyDate($this->endDate, 1), $parent->date_end, $parent->price);
        }else if ($parent->date_start == $parent->date_end) {
            $this->createInterval($this->startDate, $this->endDate, $parent->price);
        }else if ($parent->date_start < $this->startDate && $parent->date_end == $this->endDate) {
            $this->createInterval($parent->date_start, $this->modifyDate($this->startDate, 1, false), $parent->price);
            $this->createInterval($this->startDate, $this->endDate, $this->price);
        }
        $this->deleteInterval($parent->id);
    }

    /**
     * Insert a new interval in the price_intervals table
     *
     * @param $startDate
     * @parm $endDate
     * @price $price
     *
     * @return void;
     */
    private function createInterval($startDate, $endDate, $price)
    {
        $data = [
            'date_start' => $startDate,
            'date_end' => $endDate,
            'price' => $price
        ];
        PriceIntervals::create($data);
    }

    /**
     * Delete a specific interval from price_intervals table
     *
     * @param $id
     *
     * @return void;
     */
    private function deleteInterval($id)
    {
        PriceIntervals::where('id', $id)->first()->delete();
    }

    /**
     * Get the a interval that contains a new interval
     *
     * @return object;
     */
    private function getParentInterval()
    {
        return DB::table('price_intervals')
                    ->selectRaw('*')
                    ->where('date_start','<=', $this->startDate)
                    ->where('date_end','>=', $this->endDate)
                    ->first();
    }

    /**
     * Delete all intervals that are in the range of a new interval
     *
     * @return int
     */
    private function deleteMiddleInterval() {
        return DB::table('price_intervals')
            ->selectRaw('*')
            ->where('date_start','>=', $this->startDate)
            ->where('date_end','<=', $this->endDate)
            ->delete();
    }

    /**
     * Get the interval that contains the start date of a new interval
     *
     * @return object
     */
    private function getCrossStartDateInterval()
    {
        return DB::table('price_intervals')
            ->selectRaw('*')
            ->where('date_start','<=', $this->startDate)
            ->where('date_end','>=', $this->startDate)
            ->first();
    }

    /**
     * Get the interval that contains the end date of a new interval
     *
     * @return object
     */
    private function getCrossEndDateInterval()
    {
        return DB::table('price_intervals')
            ->selectRaw('*')
            ->where('date_start','<=', $this->endDate)
            ->where('date_end','>=', $this->endDate)
            ->first();
    }
}