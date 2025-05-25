<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable= [
        'firstname',
        'surname',
        'matriculation_id',
        'programme',
        'department',
        'faculty',
        'email',
        'year_of_graduation',
    ];


}
