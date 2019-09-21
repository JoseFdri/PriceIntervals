<?php
 
namespace Models;
 
use \Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class PriceIntervals extends Model {
    protected $table = 'price_intervals';
    protected $fillable = ['date_start', 'date_end', 'price'];
    public $timestamps = false;

    public static function insert($data) {
        Capsule::table('price_intervals')->insert($data);
    }
}