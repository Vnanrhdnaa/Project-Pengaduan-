<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            //tipe data foreign key disamakan dengan tipe data primary key nya
            //namanya diambil dari nametabletanpaS_primarykey
            // = : untuk memberi nilai dari variable
            // == untuk membandingkan tanpa tipe data
            // === untuk membandingkan dengan tipe data
            $table->bigInteger('report_id');
            $table->enum('status', ['ditolak', 'diterima', 'proses']);
            $table->text('pesan');
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
        Schema::dropIfExists('responses');
    }
};
