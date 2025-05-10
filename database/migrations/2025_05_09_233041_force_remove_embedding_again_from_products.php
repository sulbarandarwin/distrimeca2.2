<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'embedding')) {
                $table->dropColumn('embedding');
            }
        });
    }
    public function down(): void { /* puedes dejarlo vacío o recrear la columna */ }
};