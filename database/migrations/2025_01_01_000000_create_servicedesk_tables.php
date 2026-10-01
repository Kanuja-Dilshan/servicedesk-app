<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['departments', 'ticket_categories', 'ticket_statuses', 'ticket_priorities'] as $t) {
            Schema::create($t, function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('employee')->index(); // employee | support | admin
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
        });
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('category_id')->constrained('ticket_categories');
            $table->foreignId('priority_id')->constrained('ticket_priorities');
            $table->foreignId('status_id')->constrained('ticket_statuses');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['status_id', 'assigned_to']);
        });
        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->text('body');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
        });
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('path');
            $table->string('original_name');
            $table->timestamps();
        });
        Schema::create('ticket_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('users');
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['ticket_assignments', 'ticket_attachments', 'ticket_comments', 'tickets'] as $t) Schema::dropIfExists($t);
        Schema::table('users', fn (Blueprint $t) => $t->dropConstrainedForeignId('department_id'));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('role'));
        foreach (['ticket_priorities', 'ticket_statuses', 'ticket_categories', 'departments'] as $t) Schema::dropIfExists($t);
    }
};
