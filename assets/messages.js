'use strict';

const composer = document.querySelector('#message-composer');
const citationFields = document.querySelector('#citation-fields');

if (composer && citationFields) {
    const recipient = composer.elements.destinatario_id;
    const student = composer.elements.cita_alumno_id;
    const teacher = composer.elements.cita_docente_id;
    const fields = citationFields.querySelectorAll('input, select');
    const ids = value => (value || '').split(',').filter(Boolean);

    function filterTeachers() {
        const option = student.selectedOptions[0];
        const permitted = ids(option?.dataset.teachers);
        Array.from(teacher.options).forEach(item => {
            if (!item.value) return;
            item.hidden = !permitted.includes(item.value);
            item.disabled = item.hidden;
        });
        if (teacher.selectedOptions[0]?.disabled) teacher.value = '';
        const available = Array.from(teacher.options).filter(item => item.value && !item.disabled);
        if (available.length === 1) teacher.value = available[0].value;
    }

    function updateCitation() {
        const role = recipient.selectedOptions[0]?.dataset.role;
        const active = role === 'alumno' || role === 'padre';
        citationFields.hidden = !active;
        fields.forEach(field => {
            field.disabled = !active;
            field.required = active;
        });
        if (!active) return;

        Array.from(student.options).forEach(item => {
            if (!item.value) return;
            const matches = role === 'alumno' ? item.value === recipient.value :
                ids(item.dataset.parents).includes(recipient.value);
            item.hidden = !matches;
            item.disabled = !matches;
        });
        if (student.selectedOptions[0]?.disabled) student.value = '';
        const available = Array.from(student.options).filter(item => item.value && !item.disabled);
        if (available.length === 1) student.value = available[0].value;
        filterTeachers();
    }

    recipient.addEventListener('change', () => {
        student.value = '';
        teacher.value = '';
        updateCitation();
    });
    student.addEventListener('change', filterTeachers);
    updateCitation();
}

document.addEventListener('submit', event => {
    if (event.target.matches('[data-delete-message]') &&
        !window.confirm('¿Eliminar este mensaje de tus carpetas? La otra persona conservará su copia.')) {
        event.preventDefault();
    }
});
