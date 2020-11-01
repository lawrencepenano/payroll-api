<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTotalWorkMonthsPerYearAuditTrailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('total_work_months_per_year_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('total_work_months_per_year_id');
            $table->string('code');
            $table->string('description');
            $table->string('remarks');
            $table->string('updated_by');
            $table->string('action');
            $table->dateTime('date_and_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('total_work_months_per_year_audit_trails');
    }
}
