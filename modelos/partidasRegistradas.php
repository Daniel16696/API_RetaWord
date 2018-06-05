<?php 
class Partidas extends Illuminate\Database\Eloquent\Model{
    protected $table = "registropartidas";
    protected $primaryKey = "id";

    //desactivar created_at updated_at
    public $timestamps = false;

}
?>