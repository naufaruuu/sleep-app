<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Subservice;

class Projects extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'projects';
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

    protected static function boot()
    {
        static::bootTraits();
    }
    public function sleep($crud = false)
    {
        return '<a class="btn btn-sm btn-link" href="/admin/project/sleep/' . $this->name . '" data-toggle="tooltip" title="Sleep Project"><i class="la la-bed"></i> Sleep</a>';;
    }

    public function activate($crud = false)
    {
        return '<a class="btn btn-sm btn-link" href="/admin/project/activate/' . $this->id . '"data-toggle="tooltip" title="Activate Project"><i class="la la-play"></i> Activate</a>';;
    }

    public function refresh($crud = false)
    {
        return '<a class="btn btn-sm btn-link" href="/admin/project/refresh/' . $this->id . '"data-toggle="tooltip" title="refresh resources"><i class="la la-refresh"></i> Refresh</a>';;
    }

    public function previewButton($crud = false)
    {
        // Assuming the project's ID is accessible via $this->id
        $url = 'subservice/' . $this->name;
        return "<a class='btn btn-sm btn-link' href='{$url}' data-toggle='tooltip' title='See list'><i class='la la-book'></i> Subservices</a>";
    }
    public function refreshAll($crud = false)
    {
        // Assuming the project's ID is accessible via $this->id
        return "<a class='btn btn-info' href='/admin/project/refreshAll /' title='See list'><i class='la la-book'></i> List</a>";
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function subservices()
    {
        return $this->hasMany(Subservice::class, 'projectID', 'id');
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
