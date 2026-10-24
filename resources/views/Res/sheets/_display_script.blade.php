{{-- منطق لوحة التحكم: وضع عرض الخلايا (حسب الحقول المختارة | درجات وتقديرات | درجات فقط) وإخفاء أقسام الإحصاء --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const read = (key) => { try { return localStorage.getItem(key); } catch (e) { return null; } };
        const write = (key, value) => { try { localStorage.setItem(key, value); } catch (e) {} };

        const toggles = {
            both: document.getElementById('toggleMarks'),
            marks: document.getElementById('toggleMarksOnly'),
            rec: document.getElementById('toggleRecStats'),
            sub: document.getElementById('toggleSubStats'),
        };
        const sections = {
            rec: document.getElementById('recommendationsStatsSection'),
            sub: document.getElementById('subjectsStatsSection'),
        };

        const applyCells = () => {
            const mode = toggles.marks.checked ? 'marks' : (toggles.both.checked ? 'both' : 'default');

            document.querySelectorAll('.grade-value, .mark-value').forEach((el) => {
                const isGrade = el.classList.contains('grade-value');
                let visible = el.dataset.default === '1';

                if (mode === 'both') { visible = true; }
                if (mode === 'marks') { visible = ! isGrade; }

                el.style.display = visible ? '' : 'none';
            });
        };

        const applySection = (key) => {
            const on = toggles[key].checked;
            if (sections[key]) { sections[key].style.display = on ? '' : 'none'; }
        };

        toggles.both.checked = read('res_cells_both') === 'true';
        toggles.marks.checked = read('res_cells_marks') === 'true';
        toggles.rec.checked = read('res_rec_stats') !== 'false';
        toggles.sub.checked = read('res_sub_stats') !== 'false';

        toggles.both.addEventListener('change', () => {
            if (toggles.both.checked) { toggles.marks.checked = false; }
            write('res_cells_both', toggles.both.checked); write('res_cells_marks', toggles.marks.checked); applyCells();
        });
        toggles.marks.addEventListener('change', () => {
            if (toggles.marks.checked) { toggles.both.checked = false; }
            write('res_cells_both', toggles.both.checked); write('res_cells_marks', toggles.marks.checked); applyCells();
        });
        toggles.rec.addEventListener('change', () => { write('res_rec_stats', toggles.rec.checked); applySection('rec'); });
        toggles.sub.addEventListener('change', () => { write('res_sub_stats', toggles.sub.checked); applySection('sub'); });

        applyCells(); applySection('rec'); applySection('sub');
    });
</script>
