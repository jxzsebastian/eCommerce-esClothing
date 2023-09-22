<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Talla;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

        $productos = Producto::with(['tallas'])->get();

        $rol = Auth::user()->rol_id;
        if ($rol == 1) {
        return view('pages/productos/productos', compact('productos'));
        }
        return redirect()->route('tienda');

    }


    public function activar($id){
        $producto = Producto::findOrFail($id);
        $producto->estado = "Activo";
        $producto->save();

        return redirect()->back();

    }

    public function activarAll(){
        $productos = Producto::where('estado', 'inactivo');
        $productos->each(function ($producto) {
            $producto->estado = 'Activo';
            $producto->save();
        });

        return redirect()->route('productos');
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

    public function estadisticas(Request $request){
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

    public function create(Request $request){
        $rol = Auth::user()->rol_id;
        $tallas = Talla::get();

        if ($rol == 1) {
        return view('pages/productos/create', compact('tallas'));
        }


        return redirect()->route('tienda');

    }
    public function store(Request $request){
        $request->validate([
            'nombre' => 'required',
            'precio' => 'required|numeric|min:1',
            'cantidad' => 'required|numeric|min:1',
            'categoria' => 'required',
            'imagen' => 'required|image|mimes:jpg,png,gif',
            'tallas'=> 'required',
        ]);
        $producto = new Producto();

/*         $producto->talla_s = $request->has('talla_s');
        $producto->talla_l = $request->has('talla_l');
        $producto->talla_m = $request->has('talla_m');
        $producto->talla_xl = $request->has('talla_xl'); */
        $producto->nombre = $request->input('nombre');
        $producto->slug = Str::slug($request->input('nombre'));
        $producto->precio = $request->input('precio');
        $producto->cantidad = $request->input('cantidad');
        $producto->categoria = $request->input('categoria');

        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $rutaGuardarImagen = "productos_subidos/";
            $imgGuardado = date('YmdHis'). "." . $imagen->getClientOriginalExtension();
            $imagen->move($rutaGuardarImagen, $imgGuardado);

            $producto['imagen'] = $imgGuardado;

        }

        $tallasSeleccionada = $request->input('tallas');
        $producto->save();
        $producto->tallas()->attach($tallasSeleccionada);





        return redirect()->route('productos');

    }

    public function show($id){
        $producto = Producto::with(['tallas'])->where('estado','activo')->findOrFail($id);

        return view('pages.productos.show', compact( 'producto'));

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

    public function destroy($id){

        $producto = Producto::findOrFail($id);
        $producto->estado = "Inactivo";
        $producto->save();
        return redirect()->route('productos');

    }

    public function edit($id){
        $producto = Producto::with(['tallas'])->findOrFail($id);
        $rol = Auth::user()->rol_id;
        $tallasSeleccionadas = $producto->tallas->pluck('id')->toArray();

        $tallas = Talla::get();

        if ($rol == 1) {
            return view('pages/productos/edit', compact('producto', 'tallas', 'tallasSeleccionadas'));
        }

        return redirect()->route('tienda');

    }


    public function update(Request $request, $id){

        $producto = Producto::with(['tallas'])->findOrFail($id);

        $request->validate([
            'nombre' => 'required',
            'precio' => 'required|numeric|min:1',
            'cantidad' => 'required|numeric|min:1',
            'categoria' => 'required',
            'imagen' => 'image|mimes:jpg,png,gif',
            'tallas' => 'required',
        ]);

        $imagenAnterior = $producto->imagen;

        $producto->nombre = $request->input('nombre');
        $producto->precio = $request->input('precio');
        $producto->cantidad = $request->input('cantidad');
        $producto->categoria = $request->input('categoria');

        if ($request->hasFile('imagen')) {

            if ($imagenAnterior) {
                Storage::delete('productos_subidos/' . $imagenAnterior);
            }

            $request->validate([
                'imagen' => 'image|mimes:jpg,png,gif',
            ]);
            $imagen = $request->file('imagen');
            $rutaGuardarImagen = "productos_subidos/";
            $imgGuardado = date('YmdHis'). "." . $imagen->getClientOriginalExtension();
            $imagen->move($rutaGuardarImagen, $imgGuardado);

            $producto['imagen'] = $imgGuardado;
        }

        $tallasSeleccionadas =$request->input('tallas');;
        $producto->tallas()->sync($tallasSeleccionadas);
        
        $producto->save();

        return redirect()->route('productos');

    }
}
