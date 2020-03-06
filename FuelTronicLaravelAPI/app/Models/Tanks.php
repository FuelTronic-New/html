<?php
	namespace App\Models;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\SoftDeletes;
	class Tanks extends Model{
		use SoftDeletes;
		protected $table='tanks';
		public $fillable=array('name','optional1','grade_id','site_id','atg','manual_reading','status','initial_level','volume','min_level','cur_atg_level','last_dip_reading','cur_level_stock','litre','atg_id');
		public function grades(){
			return $this->belongsTo('App\Models\Grades','grade_id')->withTrashed();
		}
		public function hoses(){
			return $this->belongsToMany('App\Models\Hoses','hose_id');
		}
		public function grade_hoses(){
			return $this->hasMany('App\Models\Hoses','tank_id','id');
		}
		public function fuel_drops(){
			return $this->hasMany('App\Models\FuelDrop','tank_id');
		}
		public function customer_transaction(){
			return $this->hasManyThrough('App\Models\CustomerTransaction','App\Models\Hoses','tank_id','hose_id');
		}
		public function atgData(){
			return $this->belongsTo('App\Models\AtgData','atg_id');
		}
		public function site(){
			return $this->belongsTo('App\Models\Sites','site_id');
		}
		public function tankHoses(){
			return $this->hasMany('App\Models\Hoses','tank_id');
		}
		public function dashboard_tank_level()
		{
		return $this->hasMany('App\Models\DashboardTankLevel', 'TankId');
		}
	}