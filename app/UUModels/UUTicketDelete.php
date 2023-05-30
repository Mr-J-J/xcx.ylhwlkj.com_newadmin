<?php
namespace App\UUModels;

use Illuminate\Database\Eloquent\Model;

class UUTicketDelete extends Model
{
    protected $table = 'pw_ticket_delete';

    protected $fillable = ['UUaid','UUlid','UUid'];
}