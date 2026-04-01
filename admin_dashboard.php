<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Registration</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    </style>
</head>
<body>
    <div id="notification-area"></div>
    <nav class="navbar">
        <a href="admin_dashboard.php" class="navbar-brand">Smart Admin Panel</a>
        <div class="navbar-menu">
            <span class="navbar-user" id="navbar-user"></span>
            <a href="#" class="navbar-logout" id="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container dashboard-grid">
        
        <!-- Registration Reports -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Registration Overview</h2>
            </div>
            <div class="table-container">
                <table id="enrollmentReportTable">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Enrolled</th>
                            <th>Capacity</th>
                            <th>Available Seats</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add New Course Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Add New Course</h2>
            </div>
            <form id="addCourseForm" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Course Code</label>
                    <input type="text" id="courseCode" class="form-control" placeholder="e.g. CS301" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Course Name</label>
                    <input type="text" id="courseName" class="form-control" placeholder="Web Development" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Credits</label>
                    <input type="number" id="courseCredits" class="form-control" min="1" max="6" value="3" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Capacity</label>
                    <input type="number" id="courseCapacity" class="form-control" min="1" value="30" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Day</label>
                    <select id="courseDay" class="form-control" required style="background: rgba(15, 23, 42, 0.6);">
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Start Time</label>
                    <input type="time" id="courseStart" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>End Time</label>
                    <input type="time" id="courseEnd" class="form-control" required>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" id="addBtn">Add Course</button>
                </div>
            </form>
        </div>

    </div>

    <script src="js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const user = await checkAuth('admin');
            if (user) {
                loadEnrollmentReport();
            }
        });

        async function loadEnrollmentReport() {
            try {
                const res = await fetch('api/admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'course_enrollment_report' })
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    const tbody = document.querySelector('#enrollmentReportTable tbody');
                    tbody.innerHTML = '';
                    
                    data.report.forEach(c => {
                        const tr = document.createElement('tr');
                        const isFull = parseInt(c.available_seats) <= 0;
                        const statusBadge = isFull 
                            ? '<span class="badge badge-warning">Full</span>' 
                            : '<span class="badge badge-success">Open</span>';

                        tr.innerHTML = `
                            <td><strong>${c.code}</strong></td>
                            <td>${c.name}</td>
                            <td>${c.enrolled}</td>
                            <td>${c.capacity}</td>
                            <td style="${isFull ? 'color: var(--danger); font-weight: bold;' : ''}">${c.available_seats}</td>
                            <td>${statusBadge}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            } catch (err) {
                console.error(err);
                showNotification('Failed to load reports', 'error');
            }
        }

        document.getElementById('addCourseForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('addBtn');
            btn.disabled = true;
            btn.textContent = 'Adding...';

            const payload = {
                action: 'add_course',
                code: document.getElementById('courseCode').value,
                name: document.getElementById('courseName').value,
                credits: document.getElementById('courseCredits').value,
                capacity: document.getElementById('courseCapacity').value,
                schedule_day: document.getElementById('courseDay').value,
                start_time: document.getElementById('courseStart').value,
                end_time: document.getElementById('courseEnd').value
            };

            try {
                const res = await fetch('api/courses.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    showNotification(data.message, 'success');
                    document.getElementById('addCourseForm').reset();
                    loadEnrollmentReport();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (err) {
                showNotification('An error occurred while adding the course', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Add Course';
            }
        });
    </script>
</body>
</html>
