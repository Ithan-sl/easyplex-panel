<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSerieCertificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serie_certifications', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 191)->nullable();
            $table->string('certification', 191)->nullable();
            $table->string('meaning', 191)->nullable();
            $table->unsignedBigInteger('serie_id');
            $table->unsignedBigInteger('certification_id');
            $table->timestamps();

            $table->foreign('serie_id')->references('id')->on('series')->onDelete('cascade');
            $table->foreign('certification_id')->references('id')->on('certifications')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serie_certifications');
    }
}