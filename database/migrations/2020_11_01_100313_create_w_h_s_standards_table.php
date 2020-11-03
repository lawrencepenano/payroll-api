<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWHSStandardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whs_standards', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('type');
            $table->string('wd_per_year');
            $table->string('wh_per_day');
            $table->string('wm_per_year');
            $table->time('wh_start');
            $table->time('wh_end');
            $table->time('break_hours');
            $table->time('rd_monday');
            $table->time('rd_tuesday');
            $table->time('rd_wednesday');
            $table->time('rd_thursday');
            $table->time('rd_friday');
            $table->time('rd_saturday');
            $table->time('rd_sunday');
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
        Schema::dropIfExists('whs_standards');
    }
}
