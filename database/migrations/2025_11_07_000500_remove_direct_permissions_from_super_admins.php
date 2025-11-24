<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('model_has_roles') || ! Schema::hasTable('model_has_permissions')) {
            return;
        }

        $roleIds = DB::table('roles')
            ->where('name', 'Super Admin')
            ->pluck('id');

        if ($roleIds->isEmpty()) {
            return;
        }

        $userIds = DB::table('model_has_roles')
            ->whereIn('role_id', $roleIds)
            ->where('model_type', User::class)
            ->pluck('model_id');

        if ($userIds->isEmpty()) {
            return;
        }

        DB::table('model_has_permissions')
            ->where('model_type', User::class)
            ->whereIn('model_id', $userIds)
            ->delete();
    }

    public function down(): void
    {
        // No action required. Direct permissions remain removed intentionally.
    }
};
