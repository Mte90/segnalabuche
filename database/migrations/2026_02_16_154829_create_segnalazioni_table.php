<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('segnalazioni', function (Blueprint $table) {
            $table->id();
            $table->json('foto'); // array di nomi file
            $table->string('tipo'); // es. "perdita d'acqua", "tombino attappato", "buca stradale"
            $table->double('lat'); // latitudine GPS
            $table->double('lng'); // longitudine GPS
            $table->text('descrizione'); // descrizione del problema
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segnalazioni');
    }
};
