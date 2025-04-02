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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('permission')->nullable()->default(0)->after('password'); // Dodaj pole permission jako integer, może być null, domyślnie 0, po polu password
            $table->string('class')->nullable()->after('permission'); // Dodaj pole class jako string, może być null, po polu permission
            $table->boolean('active')->default(true)->after('class'); // Dodaj pole active jako boolean, domyślnie true, po polu class
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['permission', 'class', 'active']); 
        });
    }
};
