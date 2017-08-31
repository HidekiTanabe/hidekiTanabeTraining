<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MoveNumber extends Model
{
  protected $fillable = ['group','flag','move_to'];
}
