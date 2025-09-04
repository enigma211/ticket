<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
            // 1) Departments
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });

            // 2) Canned groups & replies
            Schema::create('canned_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });

            Schema::create('canned_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained('canned_groups')->cascadeOnDelete();
                $table->string('title');
                $table->text('body');
                $table->timestamps();
            });

            // 3) Users: national_id, mobile
            Schema::table('users', function (Blueprint $table) {
                $table->string('national_id', 10)->nullable()->after('email');
                $table->string('mobile')->nullable()->after('national_id');
                // Enforce uniqueness at DB level if provided
                $table->unique('national_id');
                $table->unique('mobile');
            });

            // 4) Tickets adjustments (handle SQLite vs others)
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                // Rebuild table for SQLite
                Schema::create('tickets_new', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                    $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                    $table->string('title');
                    $table->text('description');
                    $table->enum('status', ['open','awaiting_user','closed','auto_closed'])->default('open');
                    $table->unsignedBigInteger('tracking_code')->nullable();
                    $table->timestamps();
                });

                // Copy rows from old to new (map status 'new'->'open', keep tracking_code null to backfill)
                $rows = DB::table('tickets')->get();
                foreach ($rows as $r) {
                    DB::table('tickets_new')->insert([
                        'id' => $r->id,
                        'user_id' => $r->user_id,
                        'assigned_to' => $r->assigned_to,
                        'department_id' => null,
                        'title' => $r->title,
                        'description' => $r->description,
                        'status' => $r->status === 'new' ? 'open' : $r->status,
                        'tracking_code' => null,
                        'created_at' => $r->created_at,
                        'updated_at' => $r->updated_at,
                    ]);
                }

                Schema::drop('tickets');
                Schema::rename('tickets_new', 'tickets');

                Schema::table('tickets', function (Blueprint $table) {
                    $table->unique('tracking_code');
                    $table->index(['user_id','created_at']);
                    $table->index(['department_id','status','updated_at']);
                });
            } else {
                Schema::table('tickets', function (Blueprint $table) {
                    // Drop priority if exists
                    if (Schema::hasColumn('tickets', 'priority')) {
                        $table->dropColumn('priority');
                    }
                    // Adjust status enum
                    $table->enum('status', ['open','awaiting_user','closed','auto_closed'])->default('open')->change();
                    // tracking_code becomes numeric big integer
                    $table->unsignedBigInteger('tracking_code')->nullable()->change();
                    // department_id
                    $table->foreignId('department_id')->nullable()->after('assigned_to')->constrained('departments')->nullOnDelete();
                    // indexes
                    $table->index(['user_id','created_at']);
                    $table->index(['department_id','status','updated_at']);
                });
            }

            // 5) Messages visibility
            Schema::table('messages', function (Blueprint $table) {
                $table->enum('visibility', ['public','internal'])->default('public')->after('body');
            });

            // 6) Create sequence (for MySQL we simulate using an auto-increment table)
            $driver = DB::getDriverName();
            if ($driver === 'pgsql') {
                DB::statement("CREATE SEQUENCE IF NOT EXISTS ticket_tracking_code_seq START 2000 INCREMENT 1;");
            } elseif ($driver === 'mysql' || $driver === 'mariadb') {
                // Create helper table to emulate sequence
                Schema::create('ticket_tracking_sequence', function (Blueprint $table) {
                    $table->id();
                });
                DB::statement('ALTER TABLE ticket_tracking_sequence AUTO_INCREMENT = 2000;');
            } elseif ($driver === 'sqlite') {
                Schema::create('ticket_tracking_sequence', function (Blueprint $table) {
                    $table->id();
                });
                // SQLite autoincrement starts at 1; we will backfill offset via inserts later
                for ($i = 1; $i < 2000; $i++) {
                    DB::table('ticket_tracking_sequence')->insert([]);
                }
            }

            // 7) Backfill tracking_code for existing tickets
            $tickets = DB::table('tickets')->whereNull('tracking_code')->orWhere('tracking_code', '=', 0)->get(['id']);
            foreach ($tickets as $t) {
                $next = null;
                if ($driver === 'pgsql') {
                    $next = (int) DB::selectOne("SELECT nextval('ticket_tracking_code_seq') as v")->v;
                } else {
                    DB::table('ticket_tracking_sequence')->insert([]);
                    $next = (int) DB::getPdo()->lastInsertId();
                }
                DB::table('tickets')->where('id', $t->id)->update(['tracking_code' => $next]);
            }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            // Drop added columns/indexes
            Schema::table('messages', function (Blueprint $table) {
                if (Schema::hasColumn('messages', 'visibility')) {
                    $table->dropColumn('visibility');
                }
            });

            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                // Rebuild tickets back to original
                Schema::create('tickets_old', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                    $table->string('title');
                    $table->text('description');
                    $table->enum('priority', ['low','normal','high'])->default('normal');
                    $table->enum('status', ['new','open','closed'])->default('new');
                    $table->ulid('tracking_code')->unique();
                    $table->timestamps();
                });

                $rows = DB::table('tickets')->get();
                foreach ($rows as $r) {
                    DB::table('tickets_old')->insert([
                        'id' => $r->id,
                        'user_id' => $r->user_id,
                        'assigned_to' => $r->assigned_to,
                        'title' => $r->title,
                        'description' => $r->description,
                        'priority' => 'normal',
                        'status' => in_array($r->status, ['open','closed']) ? $r->status : 'new',
                        'tracking_code' => \Illuminate\Support\Str::ulid()->toBase32(),
                        'created_at' => $r->created_at,
                        'updated_at' => $r->updated_at,
                    ]);
                }

                Schema::drop('tickets');
                Schema::rename('tickets_old', 'tickets');
            } else {
                Schema::table('tickets', function (Blueprint $table) {
                    if (Schema::hasColumn('tickets', 'department_id')) {
                        $table->dropConstrainedForeignId('department_id');
                    }
                    if (Schema::hasColumn('tickets', 'tracking_code')) {
                        $table->dropUnique(['tracking_code']);
                    }
                    // Revert status enum
                    $table->enum('status', ['new','open','closed'])->default('new')->change();
                    // Re-add priority
                    $table->enum('priority', ['low','normal','high'])->default('normal');
                    // Drop added indexes
                    $table->dropIndex(['user_id','created_at']);
                    $table->dropIndex(['department_id','status','updated_at']);
                });
            }

            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'national_id')) {
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    // Drop unique if exists; fallback to index drop if needed
                    try { $table->dropUnique(['national_id']); } catch (\Throwable $e) { try { $table->dropIndex(['national_id']); } catch (\Throwable $e2) {} }
                    $table->dropColumn('national_id');
                }
                if (Schema::hasColumn('users', 'mobile')) {
                    try { $table->dropUnique(['mobile']); } catch (\Throwable $e) { try { $table->dropIndex(['mobile']); } catch (\Throwable $e2) {} }
                    $table->dropColumn('mobile');
                }
            });

            Schema::dropIfExists('canned_replies');
            Schema::dropIfExists('canned_groups');
            Schema::dropIfExists('departments');

            // Drop sequence/sequence-table
            $driver = DB::getDriverName();
            if ($driver === 'pgsql') {
                DB::statement("DROP SEQUENCE IF EXISTS ticket_tracking_code_seq;");
            } else {
                Schema::dropIfExists('ticket_tracking_sequence');
            }
    }
};


