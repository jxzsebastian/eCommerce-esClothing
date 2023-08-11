<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;
use Cart;

class Orden extends Model
{
    protected $table = 'ordenes'; // Especifica el nombre real de la tabla

    use HasFactory;
    protected $fillable = [
        'user_id',
        'subtotal',
        'session_id',
        'estado'
    ];
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'orden_productos')
            ->withPivot(['cantidad', 'precio']);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function guardarCarritoPagado($session_id){
        $orden = new Orden();

        $orden->subtotal = str_replace(',', '', Cart::subtotal());
        $orden->user_id = auth()->user()->id;
        $orden->estado = "PAGADO";
        $orden->save();
        foreach (Cart::content() as $item) {
            $orden_productos = new OrdenProducto();
            $orden_productos->precio = $item->price;
            $orden_productos->cantidad = $item->qty;
            $orden_productos->producto_id = $item->id;
            $orden_productos->orden_id = $orden->id;
            $orden_productos->save();
        }

        Cart::destroy();
    }
    public function guardarCarritoNoPagado($session_id){
        $orden = new Orden();

        $orden->subtotal = str_replace(',', '', Cart::subtotal());
        $orden->user_id = auth()->user()->id;
        $orden->session_id = $session_id;
        $orden->save();
        foreach (Cart::content() as $item) {
            $orden_productos = new OrdenProducto();
            $orden_productos->precio = $item->price;
            $orden_productos->cantidad = $item->qty;
            $orden_productos->producto_id = $item->id;
            $orden_productos->orden_id = $orden->id;
            $orden_productos->save();
        }

        Cart::destroy();
    }
}
