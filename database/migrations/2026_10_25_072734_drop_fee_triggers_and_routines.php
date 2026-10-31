<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * حساب الرسوم والمدفوعات انتقل إلى App\Services\Reg\StudentFeeService عبر مراقبَي Student وPayment،
     * فتُحذف المشغّلات والدالة والإجراء المخزَّنة (تعيق الاستضافات المشتركة وتخفي المنطق عن الاختبارات).
     */
    public function up(): void
    {
        if (! config('regulations.drop_legacy_fee_triggers') || ! $this->supportsRoutines()) {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS after_student_update');
        DB::unprepared('DROP TRIGGER IF EXISTS payments_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS payments_after_update');
        DB::unprepared('DROP PROCEDURE IF EXISTS CalculateInstallmentsWithDiscount');
        DB::unprepared('DROP FUNCTION IF EXISTS CalculateEarlyPayment');
    }

    /**
     * استعادة التعريفات السابقة (للرجوع فقط؛ تتعارض مع المراقبين فلا تُستعمل معهما).
     */
    public function down(): void
    {
        if (! $this->supportsRoutines()) {
            return;
        }

        $this->up();

        DB::unprepared('
            CREATE FUNCTION CalculateEarlyPayment(p_university_no BIGINT) RETURNS decimal(10,2)
            READS SQL DATA
            DETERMINISTIC
            BEGIN
                DECLARE v_total_payment DECIMAL(18,2) DEFAULT 0;
                SELECT COALESCE(SUM(p.amount), 0) INTO v_total_payment
                FROM payments p
                INNER JOIN students s ON p.university_no = s.university_no
                INNER JOIN reg_faculties ON reg_faculties.code = s.faculty_code
                WHERE reg_faculties.start_reg <= p.bankdate AND reg_faculties.end_reg >= p.bankdate
                  AND s.university_no = p_university_no
                  AND p.deleted_at IS NULL;
                RETURN v_total_payment;
            END
        ');

        DB::unprepared('
            CREATE PROCEDURE CalculateInstallmentsWithDiscount(
                IN p_university_no BIGINT, IN IN_fees FLOAT, IN IN_reg_fees FLOAT, IN IN_fees1 FLOAT, IN IN_fees2 FLOAT,
                IN IN_fees3 FLOAT, IN IN_fees6 FLOAT, IN IN_fees7 FLOAT, IN IN_fees9 FLOAT, IN IN_installment_code BIGINT,
                IN IN_discount_percent BIGINT, IN IN_manual_pay1 DECIMAL(10,2), IN IN_manual_pay2 DECIMAL(10,2),
                IN IN_manual_pay3 DECIMAL(10,2), IN IN_manual_pay4 DECIMAL(10,2),
                OUT H_all_fees FLOAT, OUT H_xmod FLOAT, OUT H_pay FLOAT, OUT H_fees10 FLOAT, OUT H_pay1 FLOAT,
                OUT H_pay2 FLOAT, OUT H_pay3 FLOAT, OUT H_pay4 FLOAT, OUT H_xstatus INT
            )
            BEGIN
                DECLARE v_original_regfees DECIMAL(10,2);
                DECLARE v_original_fees DECIMAL(10,2);
                DECLARE v_discounted_fees DECIMAL(10,2);
                DECLARE v_total_paied DECIMAL(10,2);
                DECLARE v_total_paied_fix DECIMAL(10,2);
                DECLARE v_xmod DECIMAL(10,2);
                DECLARE v_support DECIMAL(10,2);
                DECLARE v_total_payment DECIMAL(10,2);
                DECLARE v_installment_amount DECIMAL(10,2);
                DECLARE v_installment_no INT;
                DECLARE v_pay1 DECIMAL(10,2) DEFAULT 0;
                DECLARE v_pay2 DECIMAL(10,2) DEFAULT 0;
                DECLARE v_pay3 DECIMAL(10,2) DEFAULT 0;
                DECLARE v_pay4 DECIMAL(10,2) DEFAULT 0;
                DECLARE temp_status INT DEFAULT 20070003;

                SET v_original_regfees = IN_reg_fees + IN_fees1 + IN_fees2 + IN_fees3 + IN_fees6 + IN_fees7;
                SET v_original_fees = IN_fees;
                SET v_installment_no = RIGHT(IN_installment_code, 1);
                SET @p_discount_percent = RIGHT(IN_discount_percent, 3);

                SELECT CalculateEarlyPayment(p_university_no) INTO v_total_paied;
                SET v_total_paied_fix = v_total_paied;
                SET v_discounted_fees = v_original_fees * (1 - @p_discount_percent / 100);
                SET v_support = v_original_fees - v_discounted_fees;
                SET v_total_payment = v_original_regfees + v_discounted_fees;
                SET v_xmod = GREATEST(v_total_payment - v_total_paied, 0);

                IF v_installment_no = 7 THEN
                    SET v_pay1 = IN_manual_pay1; SET v_pay2 = IN_manual_pay2; SET v_pay3 = IN_manual_pay3; SET v_pay4 = IN_manual_pay4;
                ELSEIF v_installment_no >= 4 THEN
                    SET v_installment_amount = ROUND(v_discounted_fees / 4, 2);
                    SET v_pay1 = v_original_regfees + v_installment_amount; SET v_pay2 = v_installment_amount;
                    SET v_pay3 = v_installment_amount; SET v_pay4 = v_discounted_fees - (v_installment_amount * 3);
                ELSEIF v_installment_no = 3 THEN
                    SET v_installment_amount = ROUND(v_discounted_fees / 3, 2);
                    SET v_pay1 = v_original_regfees + v_installment_amount; SET v_pay2 = v_installment_amount;
                    SET v_pay3 = v_discounted_fees - (v_installment_amount * 2);
                ELSEIF v_installment_no = 2 THEN
                    SET v_installment_amount = ROUND(v_discounted_fees / 2, 2);
                    SET v_pay1 = v_original_regfees + v_installment_amount; SET v_pay2 = v_discounted_fees - v_installment_amount;
                ELSE
                    SET v_pay1 = v_total_payment; SET v_pay2 = 0; SET v_pay3 = 0; SET v_pay4 = 0;
                END IF;

                IF v_total_paied > 0 THEN
                    IF v_total_paied >= v_pay1 THEN
                        SET v_total_paied = v_total_paied - v_pay1; SET v_pay1 = 0;
                        IF v_total_paied >= v_pay2 THEN
                            SET v_total_paied = v_total_paied - v_pay2; SET v_pay2 = 0;
                            IF v_total_paied >= v_pay3 THEN
                                SET v_total_paied = v_total_paied - v_pay3; SET v_pay3 = 0;
                                IF v_total_paied >= v_pay4 THEN SET v_pay4 = 0;
                                ELSE SET v_pay4 = v_pay4 - v_total_paied; END IF;
                            ELSE SET v_pay3 = v_pay3 - v_total_paied; END IF;
                        ELSE SET v_pay2 = v_pay2 - v_total_paied; END IF;
                    ELSE SET v_pay1 = v_pay1 - v_total_paied; END IF;
                END IF;

                SET H_all_fees = v_total_payment; SET H_xmod = v_xmod; SET H_pay = v_total_paied_fix; SET H_fees10 = v_support;
                SET H_pay1 = v_pay1; SET H_pay2 = v_pay2; SET H_pay3 = v_pay3; SET H_pay4 = v_pay4;

                IF v_installment_no = 7 THEN SET H_pay1 = IN_fees9; END IF;

                IF H_all_fees = 0 OR v_total_paied_fix = 0 THEN SET temp_status = 20070003;
                ELSEIF v_total_paied_fix < H_all_fees THEN SET temp_status = 20070002;
                ELSE SET temp_status = 20070001; END IF;
                SET H_xstatus = temp_status;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER after_student_update BEFORE UPDATE ON students FOR EACH ROW BEGIN
                CALL CalculateInstallmentsWithDiscount(
                    NEW.university_no, NEW.fees, NEW.reg_fees, NEW.fees1, NEW.fees2, NEW.fees3, NEW.fees6, NEW.fees7, NEW.fees9,
                    NEW.installment_code, NEW.discount_percent, NEW.pay1, NEW.pay2, NEW.pay3, NEW.pay4,
                    @H_all_fees, @H_xmod, @H_pay, @H_fees10, @H_pay1, @H_pay2, @H_pay3, @H_pay4, @H_xstatus
                );
                SET NEW.all_fees = @H_all_fees, NEW.xmod = @H_xmod, NEW.pay = @H_pay, NEW.fees10 = @H_fees10,
                    NEW.pay1 = @H_pay1, NEW.pay2 = @H_pay2, NEW.pay3 = @H_pay3, NEW.pay4 = @H_pay4, NEW.xstatus = @H_xstatus;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER payments_after_insert AFTER INSERT ON payments FOR EACH ROW BEGIN
                UPDATE students SET pay = 0 WHERE university_no = NEW.university_no;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER payments_after_update AFTER UPDATE ON payments FOR EACH ROW BEGIN
                UPDATE students SET pay = CalculateEarlyPayment(NEW.university_no) WHERE university_no = NEW.university_no;
            END
        ');
    }

    private function supportsRoutines(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
};
