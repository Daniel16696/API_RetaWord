<?php 
class Usuarios extends Illuminate\Database\Eloquent\Model{
    protected $table = "usuarios";
    protected $primaryKey = "id";

    //desactivar created_at updated_at
    public $timestamps = false;

}
?>