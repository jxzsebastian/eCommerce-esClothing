<?php

namespace App\Http\Controllers;

use App\Events\NewProductsEvent;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Talla;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ProductosController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $productos = Producto::with(['tallas'])->orderBy('created_at', 'desc')->get();

        foreach($productos as $producto){
            $product = Producto::find($producto->id);
            if($product->cantidad <= 0 && $product->estado != "Inactivo"){
                $product->estado = "Inactivo";
                $product->save();
            }
        }

        $tallas = Talla::get();
        $rol = Auth::user()->rol_id;
        if ($rol == 1) {
            return view('pages/productos/productos', compact('productos', 'tallas'));
        }
        return redirect()->route('tienda');
    }


    public function activar($id)
    {
        $producto = Producto::findOrFail($id);
        if($producto->cantidad > 0){
            $producto->estado = "Activo";
            $producto->save();
            return redirect()->back()->with('update', 'ok');
        }
        return redirect()->back()->with('error', 'cantidad');

    }

    public function activarAll()
    {
        $productos = Producto::where('estado', 'inactivo');

        $productos->each(function ($producto) {
            $producto->estado = 'Activo';
            $producto->save();
        });

        return redirect()->route('productos')->with('update', 'ok');
    }

    public function search(Request $request)
    {
        $query = Producto::query();

        if ($request->has('q')) {
            $query->where('nombre', 'like', '%' . $request->input('q') . '%');
        }

        $productos = $query->paginate(5);

        return view('pages/productos/productos', compact('productos'));
    }

    public function estadisticas(Request $request)
    {
        $productosVendidos = Producto::withCount('ordenProductos')->orderBy('orden_productos_count', 'desc')->get();


        $productosConVentas = Producto::all();

        $productos_mes = Producto::selectRaw('MONTH(orden_productos.created_at) as mes, COUNT(orden_productos.id) as total_ventas')
            ->join('orden_productos', 'productos.id', '=', 'orden_productos.producto_id')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $data_barras = [];
        $data = [];

        //Diagrama de torta
        foreach ($productosConVentas as $producto) {
            $data['label'][] = $producto->nombre;
            $data['data'][] = $producto->ordenProductos->count();
        }

        //Diagrama de Barras
        foreach ($productos_mes as $producto) {
            $mesNombre = Carbon::create()->month($producto->mes)->locale('es')->monthName;
            $data_barras['label_barras'][] = $mesNombre;
            $data_barras['data_barras'][] = $producto->total_ventas;
        }

        $data_barras['data_barras'] = json_encode($data_barras);
        $data['data'] = json_encode($data);
        return view('pages/productos/estadisticas', $data, $data_barras);
    }

    public function create(Request $request)
    {
        $rol = Auth::user()->rol_id;
        $tallas = Talla::get();

        if ($rol == 1) {
            return view('pages/productos/create', compact('tallas'));
        }


        return redirect()->route('tienda');
    }
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'precio' => 'required|numeric|min:1',
            'cantidad' => 'required|numeric|min:1',
            'categoria' => 'required',
            'imagen' => 'required|image|mimes:jpg,png,jpeg',
            'tallas' => 'required_unless:categoria,Accesorios',
        ]);

        $producto = new Producto();
        $producto->nombre = $request->input('nombre');
        $producto->slug = Str::slug($request->input('nombre'));
        $producto->precio = $request->input('precio');
        $producto->cantidad = $request->input('cantidad');
        $producto->categoria = $request->input('categoria');

        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $rutaGuardarImagen = "productos_subidos/";
            $imgGuardado = date('YmdHis') . "." . $imagen->getClientOriginalExtension();
            $imagen->move($rutaGuardarImagen, $imgGuardado);

            $producto['imagen'] = $imgGuardado;
        }

        $tallasSeleccionada = $request->input('tallas');
        $producto->save();
        $producto->tallas()->attach($tallasSeleccionada);

        try {
            self::ordenNewProductNotification($producto);
        } catch (Exception $e) {
            dd($e);
        }

        return redirect()->route('productos')->with('succes', 'ok');
    }

    static function ordenNewProductNotification($producto)
    {
        //Ejemplo para conocer si las notificaciones llegan a bd o no
        /*         User::where('rol_id', 1)
        ->each(function(User $user) use ($orden){
            $user->notify(new OrdenNotification($orden));
        }); */
        event(new NewProductsEvent($producto));
    }

    public function show($id)
    {
        $producto = Producto::with(['tallas'])->findOrFail($id);

        $productosAll = Producto::with(['tallas'])->get();

        return view('pages.productos.show', compact('producto', 'productosAll'));
    }

    public function sumar(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->cantidad += 1;
        $producto->save();

        return redirect()->route('productos');
    }

    public function restar(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
        if ($producto->cantidad > 0) {
            $producto->cantidad -= 1;
            $producto->save();
        }

        return redirect()->route('productos');
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->estado = "Inactivo";
        $producto->save();
        return redirect()->route('productos')->with('update', 'ok');
    }

    public function edit($id)
    {
        $producto = Producto::with(['tallas'])->findOrFail($id);
        $rol = Auth::user()->rol_id;
        $tallasSeleccionadas = $producto->tallas->pluck('id')->toArray();

        $tallas = Talla::get();

        if ($rol == 1) {
            return view('pages/productos/edit', compact('producto', 'tallas', 'tallasSeleccionadas'));
        }

        return redirect()->route('tienda');
    }


    public function update(Request $request, $id)
    {
        $producto = Producto::with(['tallas'])->findOrFail($id);

        $request->validate([
            'nombre' => 'required',
            'precio' => 'required|numeric|min:1',
            'cantidad' => 'required|numeric|min:0',
            'estado' => 'required',
            'categoria' => 'required',
            'imagen' => 'image|mimes:jpg,png,gif|max:10000',
            'tallas' => 'required_unless:categoria,Accesorios',
        ]);

        $imagenAnterior = $producto->imagen;

        $producto->nombre = $request->input('nombre');
        $producto->precio = $request->input('precio');
        $producto->cantidad = $request->input('cantidad');
        $producto->categoria = $request->input('categoria');
        $producto->estado = $request->input('estado');
        if ($request->hasFile('imagen')) {
            if ($imagenAnterior) {
                Storage::delete('productos_subidos/' . $imagenAnterior);
            }
            $request->validate([
                'imagen' => 'image|mimes:jpg,png,gif',
            ]);
            $imagen = $request->file('imagen');
            $rutaGuardarImagen = "productos_subidos/";
            $imgGuardado = date('YmdHis') . "." . $imagen->getClientOriginalExtension();
            $imagen->move($rutaGuardarImagen, $imgGuardado);

            $producto['imagen'] = $imgGuardado;
        }

        if ($request->input('categoria') !== 'Accesorios') {
            $tallasSeleccionada = $request->input('tallas');
            $producto->tallas()->sync($tallasSeleccionada);
            $producto->save();
        } else {
            $producto->tallas()->sync([]);
            $producto->save();
        }
        return redirect()->route('productos')->with('update', 'ok');
    }
}
