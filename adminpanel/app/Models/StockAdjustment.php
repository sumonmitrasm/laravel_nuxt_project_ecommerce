<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockAdjustment extends Model {
 protected $fillable=['product_variant_id','change_quantity','stock_before','stock_after','reason','reference_type','reference_id','changed_by_type','changed_by_id','note'];
 public function variant(){return $this->belongsTo(ProductVariant::class,'product_variant_id');}
 public function reference(){return $this->morphTo();}
}