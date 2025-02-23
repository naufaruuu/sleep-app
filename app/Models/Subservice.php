<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Projects;
use App\Models\Resources;


class Subservice extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'subservices';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];

    protected static function boot()
    {
        static::bootTraits();

    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function sleep($crud = false)
    {
	    return '<a class="btn btn-sm btn-link" href="/admin/subservice/sleep/' . $this->id . '"data-toggle="tooltip" title="Sleep Project"><i class="la la-bed"></i> Sleep</a>';;
    }

    public function activate($crud = false)
    {
	    return '<a class="btn btn-sm btn-link" href="/admin/subservice/activate/' . $this->id . '"data-toggle="tooltip" title="Activate Project"><i class="la la-bed"></i> Activate</a>';;
    }

    public function refresh($crud = false)
    {
	    return '<a class="btn btn-sm btn-link" href="/admin/subservice/refresh/' . $this->id . '"data-toggle="tooltip" title="refresh resources"><i class="la la-refresh"></i> Refresh</a>';;
    }

    public function previewButton($crud = false)
    {
    	// Assuming the project's ID is accessible via $this->id
    	$url = '/admin/resources/' . $this->name ;
    	return "<a class='btn btn-sm btn-link' href='{$url}' data-toggle='tooltip' title='See resources'><i class='la la-book'></i> Resources</a>";
    }


    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function project()
    {
        return $this->belongsTo(Projects::class, 'projectID', 'id');
    }

    public function resources()
    {
        return $this->hasMany(Resources::class, 'subserviceID', 'id');
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
