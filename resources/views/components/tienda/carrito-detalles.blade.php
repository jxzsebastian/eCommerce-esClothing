<div class="bg-white">
    <div class="max-w-2xl mx-auto pt-10 pb-24 px-4 sm:px-6 lg:max-w-7xl lg:px-8">
        <h1 class="text-xl font-extrabold tracking-tight text-gray-900 sm:text-3xl">Carrito de Compras </h1>
        <div class="mt-12 lg:grid lg:grid-cols-12 lg:gap-x-12 lg:items-start xl:gap-x-16">
            <section aria-labelledby="cart-heading" class="lg:col-span-7">
                <h2 id="cart-heading" class="sr-only">Items del carrito de Compras</h2>

                <ul role="list" class="border-t border-b border-gray-200 divide-y divide-gray-200">
                    @foreach (Cart::content() as $item)
                        <li class="flex py-6 sm:py-10">
                            <div class="flex-shrink-0">
                                <img src="{{ url('productos_subidos') }}/{{ $item->options->urlfoto }}"
                                    alt="imagen."
                                    class="w-24 h-24 rounded-md object-center object-cover sm:w-48 sm:h-48">
                            </div>

                            <div class="ml-4 flex-1 flex flex-col justify-between sm:ml-6">
                                <div class="relative pr-9 sm:grid sm:grid-cols-2 sm:gap-x-6 sm:pr-0">
                                    <div>
                                        <div class="flex justify-between">
                                            <h3 class="text-sm">
                                                <a href="#"
                                                    class="font-medium text-gray-700 hover:text-gray-800">
                                                    {{ $item->name }} </a>
                                            </h3>
                                        </div>
                                        <div class="mt-1 flex text-sm">
                                            <p class="text-gray-500">{{ $item->options->categoria }}</p>

                                            <p class="ml-4 pl-4 border-l border-gray-200 text-gray-500">
                                                {{ $item->options->talla }}</p>
                                        </div>
                                        <p class="mt-1 text-sm font-medium text-gray-900">
                                            ${{ number_format($item->qty * $item->price, 2) }}</p>
                                    </div>

                                    <div class=" block mt-4 sm:mt-0 sm:pr-9">
                                        <label for="quantity-0" class="sr-only">Cantidad</label>

                                        <div class="flex items-center border-gray-100">
                                            <a href="{{ route('decrementarcantidad', ['id' => $item->rowId]) }}"
                                                class="cursor-pointer rounded-l bg-gray-100 py-1 px-3.5 duration-100 hover:bg-blue-500 hover:text-blue-50">
                                                - </a>
                                            <input disabled
                                                class="h-8 w-12  bg-white  text-xs outline-none border border-gray-300 text-base focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                type="number" value="{{ $item->qty }}" min="1" >

                                            <a href="{{ route('incrementarcantidad', ['id' => $item->rowId]) }}"
                                                class="cursor-pointer rounded-r bg-gray-100 py-1 px-3.5 duration-100 hover:bg-blue-500 hover:text-blue-50">
                                                + </a>
                                        </div>
                                        <div class="absolute top-0 right-0">
                                            <a href="{{ route('eliminaritem', ['id' => $item->rowId]) }}"
                                                type="button"
                                                class="-m-2 p-2 inline-flex text-gray-400 hover:text-gray-500">
                                                <span class="sr-only">Eliminar</span>
                                                <!-- Heroicon name: solid/x -->
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" fill="currentColor"
                                                    aria-hidden="true">
                                                    <path fill-rule="evenodd"
                                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <p class="mt-4 flex text-sm text-gray-700 space-x-2">
                                    <!-- Heroicon name: solid/check -->
                                    <svg class="flex-shrink-0 h-5 w-5 text-green-500"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>In stock</span>
                                </p>
                            </div>
                        </li>
                    @endforeach
                    @if (!Cart::content()->count())
                        <div
                            class="justify-between mb-6 rounded-lg bg-white p-6 shadow-md sm:flex sm:justify-start">
                            <div class="sm:ml-4 sm:flex sm:w-full sm:justify-between">
                                <div class="mt-5 sm:mt-0 ">
                                    <h2 class="text-xl font-bold text-gray-900 ">No existen productos
                                        dentro
                                        del
                                        Carrito.</h2>
                                    <p class=" mt-1 text-s text-gray-700">¡Ve a la <a
                                            href="{{ route('tienda') }}"
                                            class="underline text-sky-400/100">Tienda</a> y empieza a
                                        agregar
                                        productos a tu carrito!</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </ul>
            </section>

            <!-- Order summary -->
            @if (Cart::content()->count())
                <section aria-labelledby="summary-heading"
                    class="mt-16 bg-gray-50 rounded-lg px-4 py-6 sm:p-6 lg:p-8 lg:mt-0 lg:col-span-5">
                    <h2 id="summary-heading" class="text-lg font-medium text-gray-900">Resumen Compra</h2>

                    <dl class="mt-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-600">Subtotal</dt>
                            <dd class="text-sm font-medium text-gray-900">${{ Cart::subtotal() }}</dd>
                        </div>

                        <div class="border-t border-gray-200 pt-4 flex items-center justify-between">
                            <dt class="flex text-sm text-gray-600">
                                <span>IVA 19%</span>
                                <a href="#"
                                    class="ml-2 flex-shrink-0 text-gray-400 hover:text-gray-500">
                                    <span class="sr-only">Learn more about how tax is calculated</span>
                                </a>
                            </dt>
                            <dd class="text-sm font-medium text-gray-900">${{ Cart::tax() }}</dd>
                        </div>
                        <div class="border-t border-gray-200 pt-4 flex items-center justify-between">
                            <dt class="text-base font-medium text-gray-900">Total de la Compra</dt>
                            <dd class="text-base font-medium text-gray-900">${{ Cart::total() }}</dd>
                        </div>
                    </dl>


                    @switch(True)
                        @case(Auth::check())
                            @if (auth()->user()->direccion &&
                                    auth()->user()->ciudad &&
                                    auth()->user()->pais &&
                                    auth()->user()->codigo_postal &&
                                    auth()->user()->numero_telefono)
                                <form action="{{ url('/session') }}" method="POST">
                                    @csrf
                                    <button
                                        class="mt-6 w-full bg-indigo-600 border border-transparent rounded-md shadow-sm py-2 px-4 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-indigo-50 focus:ring-indigo-500">Continuar
                                        Compra
                                    </button>
                                </form>
                                <form action="{{ route('eliminarcarrito') }}">
                                    <button
                                        class="mt-6 w-full bg-gray-600 border border-transparent rounded-md shadow-sm py-2 px-4 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-gray-500">Vaciar
                                        Carrito
                                    </button>
                                </form>

                                <x-tienda.datos-envio />
                            @else
                                <button onclick="openModal()"
                                    class="bg-white w-full mt-3 hover:bg-gray-100 text-gray-800 font-medium py-2 px-3 border border-gray-400 rounded shadow">
                                    Completa tu Informacion de Usuario
                                </button>
                                <p class="text-sm mt-2 text-gray-700">¡Para hacer un pedido debes tener todos tus
                                    datos
                                    de
                                    usuarios!</p>

                                <x-tienda.datos-envio />
                            @endif
                        @break

                        @default
                            <form action="{{ route('login') }}">
                                <button
                                    class="bg-white w-full mt-3 hover:bg-gray-100 text-gray-800 font-semibold py-2 px-3 border border-gray-400 rounded shadow">
                                    Inicia Sesión
                                </button>
                            </form>
                            <p class="text-sm mt-2 text-gray-700">¡Para hacer un pedido debes antes Iniciar Sesión!
                            </p>
                    @endswitch

                </section>
            @endif

        </div>
    </div>
</div>