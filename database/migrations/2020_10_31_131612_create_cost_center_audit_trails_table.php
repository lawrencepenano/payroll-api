<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCostCenterAuditTrailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cost_center_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('cost_center_id');
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
        Schema::dropIfExists('cost_center_audit_trails');
    }
}
