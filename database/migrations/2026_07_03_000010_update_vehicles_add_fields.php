<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('chassis_number', 100)->nullable()->after('color');
            $table->string('vehicle_image')->nullable()->after('chassis_number');
            $table->string('registration_image')->nullable()->after('vehicle_image');
            $table->string('insurance_image')->nullable()->after('registration_image');
            // Registration (الاستمارة)
            $table->string('registration_number', 50)->nullable()->after('insurance_image');
            $table->date('registration_issue_date')->nullable()->after('registration_number');
            $table->date('registration_expiry_date')->nullable()->after('registration_issue_date');
            // Insurance (التأمين)
            $table->string('insurance_company', 150)->nullable()->after('registration_expiry_date');
            $table->string('insurance_policy_number', 50)->nullable()->after('insurance_company');
            $table->date('insurance_start_date')->nullable()->after('insurance_policy_number');
            $table->date('insurance_expiry_date')->nullable()->after('insurance_start_date');
            // Periodic Inspection (الفحص الدوري)
            $table->string('inspection_number', 50)->nullable()->after('insurance_expiry_date');
            $table->date('inspection_issue_date')->nullable()->after('inspection_number');
            $table->date('inspection_expiry_date')->nullable()->after('inspection_issue_date');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'chassis_number', 'vehicle_image', 'registration_image', 'insurance_image',
                'registration_number', 'registration_issue_date', 'registration_expiry_date',
                'insurance_company', 'insurance_policy_number', 'insurance_start_date', 'insurance_expiry_date',
                'inspection_number', 'inspection_issue_date', 'inspection_expiry_date',
            ]);
        });
    }
};
