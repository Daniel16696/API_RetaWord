<?php 
class Preguntas extends Illuminate\Database\Eloquent\Model{
    protected $table = "preguntas";
    protected $primaryKey = "id";

    //desactivar created_at updated_at
    public $timestamps = false;

}
?>