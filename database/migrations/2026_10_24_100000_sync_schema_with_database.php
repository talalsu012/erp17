<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * مزامنة المخطط مع قاعدة البيانات الفعلية: أعمدة وجداول موجودة في القاعدة ولم تكن في ملفات الـ migrations.
     * كل خطوة محمية بفحص وجود حتى تعمل على قاعدة جديدة وعلى القاعدة الحالية.
     */
    public function up(): void
    {
        $this->syncSettingsTables();
        $this->syncStudentAndSubjectTables();
        $this->syncAttendanceTables();
        $this->syncResultTables();
        $this->createMissingTables();
    }

    public function down(): void
    {
        foreach (['subject_approval_statuses', 'reg_faculty_user', 'prints', 'cert_style', 'cert_students', 'cert_setting', 'att_lecture_student', 'att_lecture_staff'] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function addColumnIfMissing(string $table, string $column, Closure $definition): void
    {
        if (! Schema::hasColumn($table, $column)) {
            Schema::table($table, $definition);
        }
    }

    private function syncSettingsTables(): void
    {
        $this->addColumnIfMissing('reg_campuses', 'redsea', fn (Blueprint $table) => $table->string('redsea')->nullable());
        $this->addColumnIfMissing('reg_departments', 'metadata', fn (Blueprint $table) => $table->longText('metadata')->nullable());
        $this->addColumnIfMissing('reg_faculties', 'metadata', fn (Blueprint $table) => $table->longText('metadata')->nullable());
        $this->addColumnIfMissing('reg_faculties', 'logo', fn (Blueprint $table) => $table->string('logo', 50)->nullable());
        $this->addColumnIfMissing('reg_levels', 'parent_code', fn (Blueprint $table) => $table->unsignedBigInteger('parent_code')->nullable());
        $this->addColumnIfMissing('res_resons', 'deleted_at', fn (Blueprint $table) => $table->softDeletes());
        $this->addColumnIfMissing('res_ltrecoms', 'deleted_at', fn (Blueprint $table) => $table->softDeletes());
    }

    private function syncStudentAndSubjectTables(): void
    {
        $this->addColumnIfMissing('students', 'student_account', fn (Blueprint $table) => $table->unsignedBigInteger('student_account')->nullable());

        $this->addColumnIfMissing('subjects', 'special_code', function (Blueprint $table): void {
            $table->unsignedBigInteger('special_code')->nullable();
            $table->foreign('special_code', 'FK_subjects_reg_specials')->references('code')->on('reg_specials')->cascadeOnUpdate();
        });
    }

    private function syncAttendanceTables(): void
    {
        $this->addColumnIfMissing('att_lectures', 'deleted_at', fn (Blueprint $table) => $table->softDeletes());

        $this->addColumnIfMissing('att_staff', 'crch_load', fn (Blueprint $table) => $table->unsignedTinyInteger('crch_load')->nullable());
        $this->addColumnIfMissing('att_staff', 'user_id', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'FK_att_staff_users')->references('id')->on('users')->cascadeOnUpdate();
        });

        $this->addColumnIfMissing('att_staff_subject', 'batch_code', function (Blueprint $table): void {
            $table->unsignedBigInteger('batch_code')->nullable();
            $table->foreign('batch_code', 'FK_att_staff_subject_reg_ltcolumns')->references('code')->on('reg_ltcolumns')->cascadeOnUpdate();
        });
    }

    private function syncResultTables(): void
    {
        if (Schema::hasColumn('res_marks', 'approval_status')) {
            Schema::table('res_marks', fn (Blueprint $table) => $table->dropColumn('approval_status'));
        }

        $this->addColumnIfMissing('res_marks', 'department_code', function (Blueprint $table): void {
            $table->unsignedBigInteger('department_code')->nullable()->after('faculty_code');
            $table->unsignedBigInteger('special_code')->nullable()->after('department_code');
            $table->foreign('department_code', 'FK_res_marks_reg_departments')->references('code')->on('reg_departments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('special_code', 'FK_res_marks_reg_specials')->references('code')->on('reg_specials')->cascadeOnUpdate()->cascadeOnDelete();
        });

        if (Schema::hasColumn('res_recoms', 'gpa')) {
            return;
        }

        $recomForeignKey = collect(Schema::getForeignKeys('res_recoms'))
            ->first(fn (array $foreignKey): bool => $foreignKey['columns'] === ['recom_code']);

        Schema::table('res_recoms', function (Blueprint $table) use ($recomForeignKey): void {
            if ($recomForeignKey !== null) {
                $table->dropForeign($recomForeignKey['name']);
            }

            $table->dropColumn(['cfail', 'cabs', 'subject_fail', 'subject_abs', 'pga', 'cpga']);
        });

        Schema::table('res_recoms', function (Blueprint $table): void {
            $table->unsignedBigInteger('department_code')->nullable()->after('faculty_code');
            $table->unsignedBigInteger('special_code')->nullable()->after('department_code');
            $table->integer('countSubject')->nullable()->after('examType');
            $table->integer('sumMarks')->nullable()->after('countSubject');
            $table->integer('s_pass')->nullable()->after('sumMarks');
            $table->integer('s_fail')->nullable()->after('s_pass');
            $table->integer('crch_fail')->nullable()->after('s_fail');
            $table->integer('s_abs')->nullable()->after('crch_fail');
            $table->float('gpa')->nullable()->after('points');
            $table->float('cgpa')->nullable()->after('gpa');

            $table->foreign('department_code', 'FK_res_recoms_reg_departments')->references('code')->on('reg_departments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('special_code', 'FK_res_recoms_reg_specials')->references('code')->on('reg_specials')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unique(['university_no', 'faculty_code', 'department_code', 'level_code', 'batch_code', 'semester', 'examType'], 'unique_student_recom');
        });
    }

    private function createMissingTables(): void
    {
        if (! Schema::hasTable('att_lecture_staff')) {
            Schema::create('att_lecture_staff', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('lecture_id');
                $table->tinyInteger('is_excutive')->nullable()->default(0);
                $table->date('date_excutive')->nullable();
                $table->time('actual_excutive_start_time')->nullable();
                $table->time('actual_excutive_end_time')->nullable();
                $table->timestamps();

                $table->foreign('lecture_id', 'FK_att_lecture_staff_att_lectures')->references('id')->on('att_lectures')->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('att_lecture_student')) {
            Schema::create('att_lecture_student', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('lecture_id');
                $table->unsignedBigInteger('university_no');
                $table->enum('status', ['present', 'absent', 'late']);
                $table->timestamps();

                $table->index(['lecture_id', 'university_no', 'status'], 'lecture_id_universit_no_status');
                $table->foreign('lecture_id')->references('id')->on('att_lectures')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreign('university_no')->references('university_no')->on('students')->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('cert_setting')) {
            Schema::create('cert_setting', function (Blueprint $table): void {
                $table->id();
                $table->string('name_ar');
                $table->string('name_en');
                $table->string('slug')->unique('cert_setting_slaug_unique');
                $table->string('groups');
                $table->unsignedBigInteger('faculty_code')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->boolean('is_visible')->default(false);
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('faculty_code', 'FK_cert_setting_reg_faculties')->references('code')->on('reg_faculties')->cascadeOnUpdate();
                $table->foreign('parent_id', 'FK_cert_setting_cert_setting')->references('id')->on('cert_setting')->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('cert_students')) {
            Schema::create('cert_students', function (Blueprint $table): void {
                $table->id();
                $table->string('universityno')->nullable();
                $table->string('name_ar');
                $table->string('slug')->nullable()->unique();
                $table->string('verify_hash')->nullable();
                $table->string('name_en');
                $table->unsignedBigInteger('gender_code')->nullable();
                $table->date('alumia_date');
                $table->string('hijri_date')->nullable();
                $table->unsignedBigInteger('admission_code')->nullable()->index();
                $table->unsignedBigInteger('academic_year_code')->nullable();
                $table->unsignedBigInteger('stady_code')->nullable();
                $table->unsignedBigInteger('degree_id')->index();
                $table->unsignedBigInteger('grade_id')->nullable()->index();
                $table->unsignedBigInteger('faculty_code');
                $table->string('department_code')->nullable();
                $table->unsignedBigInteger('special_code')->nullable();
                $table->unsignedBigInteger('nationalty_code')->nullable();
                $table->boolean('is_visible')->default(true);
                $table->string('meeting_no')->nullable();
                $table->string('remarks')->nullable();
                $table->string('remarks_en')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('faculty_code', 'FK_cert_students_reg_faculties')->references('code')->on('reg_faculties')->cascadeOnUpdate();
                $table->foreign('gender_code', 'FK_cert_students_reg_ltcolumns')->references('code')->on('reg_ltcolumns')->cascadeOnUpdate();
                $table->foreign('nationalty_code', 'FK_cert_students_reg_ltcolumns_2')->references('code')->on('reg_ltcolumns')->cascadeOnUpdate();
                $table->foreign('stady_code', 'FK_cert_students_reg_ltcolumns_3')->references('code')->on('reg_ltcolumns')->cascadeOnUpdate();
                $table->foreign('academic_year_code', 'FK_cert_students_reg_ltcolumns_4')->references('code')->on('reg_ltcolumns')->cascadeOnUpdate();
                $table->foreign('special_code', 'FK_cert_students_reg_specials')->references('code')->on('reg_specials')->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('cert_style')) {
            Schema::create('cert_style', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('key', 50)->default('0');
                $table->string('value')->nullable();
                $table->string('group', 50)->default('0');
                $table->string('cert', 50)->default('transcript');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('prints')) {
            Schema::create('prints', function (Blueprint $table): void {
                $table->id();
                $table->string('fields', 30);
                $table->string('name_ar', 30);
                $table->string('name_en', 30);
                $table->integer('type')->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('reg_faculty_user')) {
            Schema::create('reg_faculty_user', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('faculty_code')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('subject_approval_statuses')) {
            Schema::create('subject_approval_statuses', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('subject_code')->nullable();
                $table->unsignedBigInteger('batch_code');
                $table->enum('current_stage', ['professor', 'head_department', 'dean', 'scientific_affairs', 'approved', 'rejected'])->default('professor');
                $table->unsignedBigInteger('current_user_id')->nullable();
                $table->unsignedBigInteger('last_approved_by')->nullable();
                $table->timestamp('last_approved_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['subject_code', 'batch_code'], 'unique_subject_batch');
                $table->foreign('batch_code', 'FK_subject_approval_statuses_reg_ltcolumns')->references('code')->on('reg_ltcolumns')->cascadeOnUpdate();
                $table->foreign('subject_code', 'FK_subject_approval_statuses_subjects')->references('code')->on('subjects')->cascadeOnUpdate();
                $table->foreign('current_user_id', 'FK_subject_approval_statuses_users')->references('id')->on('users')->cascadeOnUpdate();
            });
        }
    }
};
