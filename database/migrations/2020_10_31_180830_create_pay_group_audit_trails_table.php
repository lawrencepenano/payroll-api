<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayGroupAuditTrailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_group_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('pay_group_id');
            $table->string('code');
            $table->string('description');
            $table->string('remarks');
            $table->string('updated_by');
            $table->string('action');
            $table->dateTime('date_and_time');
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
        Schema::dropIfExists('pay_group_audit_trails');
    }
}
