document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!confirm(form.dataset.confirm || 'Are you sure?')) {
                event.preventDefault();
            }
        });
    });

    const loginEmail = document.getElementById('loginEmail');
    document.querySelectorAll('input[name="login_role"][data-email]').forEach((role) => {
        role.addEventListener('change', () => {
            if (loginEmail && role.checked) {
                loginEmail.value = role.dataset.email;
            }
        });
    });

    const makeBarChart = (id, labels, values, label) => {
        const node = document.getElementById(id);
        if (!node || typeof Chart === 'undefined') return;
        new Chart(node, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label,
                    data: values,
                    borderWidth: 1,
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true, max: 5 } }
            }
        });
    };

    if (window.sfsReports) {
        makeBarChart('facultyChart', window.sfsReports.faculty.map(x => x.full_name), window.sfsReports.faculty.map(x => Number(x.avg_rating || 0)), 'Average rating');
        makeBarChart('subjectChart', window.sfsReports.subjects.map(x => x.subject_name), window.sfsReports.subjects.map(x => Number(x.avg_rating || 0)), 'Average rating');
        makeBarChart('semesterChart', window.sfsReports.semesters.map(x => `Semester ${x.semester}`), window.sfsReports.semesters.map(x => Number(x.avg_rating || 0)), 'Average rating');
        makeBarChart('criteriaChart', window.sfsReports.criteria.map(x => x.criteria_name), window.sfsReports.criteria.map(x => Number(x.avg_score || 0)), 'Average score');
    }

    if (window.sfsFacultySubjects) {
        makeBarChart('facultySelfChart', window.sfsFacultySubjects.map(x => x.subject_name), window.sfsFacultySubjects.map(x => Number(x.avg_rating || 0)), 'Average rating');
    }

    if (window.sfsFacultyCriteria) {
        makeBarChart('facultyCriteriaChart', window.sfsFacultyCriteria.map(x => x.criteria_name), window.sfsFacultyCriteria.map(x => Number(x.avg_score || 0)), 'Average score');
    }
});
