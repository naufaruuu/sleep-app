<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Subservice;

class Resources extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        static::bootTraits();
    }

    protected $table = 'resources';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function sleep($crud = false)
    {
	    return '<a class="btn btn-sm btn-link" href="/admin/resource/sleep/' . $this->id . '"data-toggle="tooltip" title="Sleep Project"><i class="la la-bed"></i> Sleep</a>';;
    }

    public function activate($crud = false)
    {
	    return '<a class="btn btn-sm btn-link" href="/admin/resource/activate/' . $this->id . '"data-toggle="tooltip" title="Activate Project"><i class="la la-bed"></i> Activate</a>';;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function subservice()
    {
        return $this->belongsTo(Subservice::class, 'subserviceID');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
