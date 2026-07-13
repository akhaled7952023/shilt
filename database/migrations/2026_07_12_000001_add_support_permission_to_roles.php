<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roles = DB::table('roles')->get(['id', 'permissions']);

        foreach ($roles as $role) {
            $perms = json_decode($role->permissions, true);

            if (! is_array($perms) || in_array('support', $perms)) {
                continue;
            }

            $perms[] = 'support';

            DB::table('roles')
                ->where('id', $role->id)
                ->update(['permissions' => json_encode(array_values($perms))]);
        }
    }

    public function down(): void
    {
        $roles = DB::table('roles')->get(['id', 'permissions']);

        foreach ($roles as $role) {
            $perms = json_decode($role->permissions, true);

            if (! is_array($perms)) {
                continue;
            }

            $filtered = array_values(array_filter($perms, fn ($p) => $p !== 'support'));

            DB::table('roles')
                ->where('id', $role->id)
                ->update(['permissions' => json_encode($filtered)]);
        }
    }
};
