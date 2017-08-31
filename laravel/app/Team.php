<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Team extends Model
{
    protected $fillable = ['team_no','user_id','create_date_id','move_number_id','userName','move_no'];
}
