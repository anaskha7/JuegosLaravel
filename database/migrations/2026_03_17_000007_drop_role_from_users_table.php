<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // El proyecto ya no guarda los roles directamente en users.
        // Los roles se gestionan mediante las tablas roles y role_user.
    }

    public function down(): void
    {
        // No hace falta una accion de rollback porque el esquema final
        // debe mantener los roles mediante la relacion pivote.
    }
};
