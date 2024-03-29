<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('estado')->default('Activo');
            $table->string('nombre', 2000);
            $table->string('categoria', 2000);
            $table->decimal('precio');
            $table->integer('cantidad');
            $table->string('slug', 2000)->nullable();
            $table->string('imagen', 2000)->nullable()->mimes('jpg', 'jpeg', 'png');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
