<?php
$timestamp = date('Y_m_d_His');
$file = "database/migrations/{$timestamp}_add_is_demo_to_providers.php";

$content = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('providers', 'is_demo')) {
            Schema::table('providers', function (Blueprint $table) {
                $table->boolean('is_demo')->default(false)->after('is_active');
                $table->index('is_demo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('providers', 'is_demo')) {
            Schema::table('providers', function (Blueprint $table) {
                $table->dropColumn('is_demo');
            });
        }
    }
};
PHP;

file_put_contents($file, $content);
echo "✅ Migration created: {$file}\n";