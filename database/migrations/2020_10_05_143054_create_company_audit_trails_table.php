<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyAuditTrailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->text('company_logo');
            $table->string('company_name');
            $table->string('nature_of_business');
            $table->string('address1');
            $table->string('address2');
            $table->string('zip_code');
            $table->string('rdo');
            $table->string('phone');
            $table->string('fax');
            $table->string('tin_no');
            $table->string('sss_no');
            $table->string('hdmf_no');
            $table->string('working_hours');
            $table->string('working_hours_schedule_type');
            $table->string('created_by');
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
        Schema::dropIfExists('company_audit_trails');
    }
}
