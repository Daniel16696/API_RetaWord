<?php 
class Imagenes extends Illuminate\Database\Eloquent\Model{
    protected $table = "imagenesparausuarios";
    protected $primaryKey = "id";

    //desactivar created_at updated_at
    public $timestamps = false;

}
?>