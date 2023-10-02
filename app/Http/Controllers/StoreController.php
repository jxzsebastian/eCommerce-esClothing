<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Carbon\Carbon;
use App\Models\Orden;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    //

    public function index(Request $request){


        $producto_randoms = Producto::with(['tallas'])->where('estado', 'Activo')->inRandomOrder()
        ->limit(4)
        ->get();

        $productos_top  = Producto::with(['tallas'])->where('estado', 'Activo')->inRandomOrder()
        ->limit(4)
        ->get();

        return view('pages/store/tienda', compact('producto_randoms', 'productos_top'));

    }

    public function catalogoVista(Request $request){

        $categoria = $request->categoria;

        $productos_categoria = Producto::with(['tallas'])->where('categoria', $categoria)->where('estado', 'activo')->paginate(12);
        $productoReciente = Producto::with(['tallas'])->latest('created_at')->where('categoria', $categoria)->where('estado', 'activo')->first();

        if (!$request->categoria) {
        $productos_categoria = Producto::with(['tallas'])->where('estado', 'activo')->paginate(12);
        $productoReciente = Producto::with(['tallas'])->latest('created_at')->where('estado', 'activo')->first();
        }

        return view('pages/store/catalogo', compact('productos_categoria', 'productoReciente'));

    }

    public function pedidos_hechos(){
        $user_id = Auth::user()->id;

        $pedidos = Orden::with('productos', 'user')->where('user_id', $user_id)->orderByDesc('created_at')->paginate(2);


        return view('pages/store/pedidos', compact('pedidos'));
    }

    public function pedidoInformacion($id){

        $pedido = Orden::findOrFail($id)
        ->with('productos', 'user')->where('id', $id)->get();
        $cont = 1;
        return view('pages/store/detalle', compact('pedido', 'cont'));


    }

}
