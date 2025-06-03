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
        Schema::create('ldap_users', function (Blueprint $table) {
            $table->id();
            $table->string('dn')->unique(); // Distinguished Name
            $table->string('cn');
            $table->string('givenname')->nullable();
            $table->string('sn')->nullable();
            $table->string('mail')->nullable();
            $table->string('samaccountname')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('ldap_groups', function (Blueprint $table) {
            $table->id();
            $table->string('dn')->unique(); // Distinguished Name
            $table->string('cn');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ldap_groups');
        Schema::dropIfExists('ldap_users');
    }
};
