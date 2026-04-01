<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Smart Registration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div id="notification-area"></div>
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">Smart Registration</a>
        <div class="navbar-menu">
            <span class="navbar-user" id="navbar-user"></span>
            <a href="#" class="navbar-logout" id="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container dashboard-grid">
        <!-- Left Column: Available Courses -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Available Courses</h2>
            </div>
            <div class="table-container">
                <table id="coursesTable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Course Name</th>
                            <th>Credits</th>
                            <th>Schedule</th>
                            <th>Seats Remaining</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Courses will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Column: My Schedule -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">My Schedule</h2>
                <button class="btn btn-primary" onclick="window.print()" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; width: auto;" id="printBtn">🖨️ Download / Print</button>
            </div>
            <div id="myScheduleList">
                <!-- Schedule loaded dynamically -->
                <p style="color: var(--text-secondary); font-size: 0.9rem;">Loading schedule...</p>
            </div>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const user = await checkAuth('student');
            if (user) {
                loadAvailableCourses();
                loadMySchedule();
            }
        });

        async function loadAvailableCourses() {
            try {
                const res = await fetch('api/courses.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'fetch_all' })
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    const tbody = document.querySelector('#coursesTable tbody');
                    tbody.innerHTML = '';
                    
                    data.courses.forEach(c => {
                        const tr = document.createElement('tr');
                        
                        let prereqsText = 'None';
                        if (c.prerequisites && c.prerequisites.length > 0) {
                            prereqsText = c.prerequisites.map(p => `<span class="badge badge-warning" title="${p.name}">${p.code}</span>`).join(' ');
                        }

                        tr.innerHTML = `
                            <td><strong>${c.code}</strong><br><small style="margin-top:4px; display:block;">Require: ${prereqsText}</small></td>
                            <td>${c.name}</td>
                            <td>${c.credits}</td>
                            <td>${c.schedule_day}<br><small>${c.start_time.substring(0,5)} - ${c.end_time.substring(0,5)}</small></td>
                            <td>${c.capacity} total</td>
                            <td>
                                <button class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="registerCourse(${c.id})">Register</button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            } catch (err) {
                console.error(err);
                showNotification('Failed to load courses', 'error');
            }
        }

        async function loadMySchedule() {
            const list = document.getElementById('myScheduleList');
            try {
                const res = await fetch('api/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'my_schedule' })
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    if (data.schedule.length === 0) {
                        list.innerHTML = '<p style="color: var(--text-secondary); font-size: 0.9rem;">You are not registered for any courses yet.</p>';
                        return;
                    }

                    list.innerHTML = data.schedule.map(c => `
                        <div style="background: rgba(15, 23, 42, 0.4); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid var(--border);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <strong>${c.code}</strong>
                                <span class="badge badge-success">Registered</span>
                            </div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.75rem;">
                                ${c.name} • ${c.credits} Credits<br>
                                ${c.schedule_day} ${c.start_time.substring(0,5)} - ${c.end_time.substring(0,5)}
                            </div>
                            <button class="btn btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.75rem; width: auto;" onclick="dropCourse(${c.id})">Drop Course</button>
                        </div>
                    `).join('');
                }
            } catch (err) {
                console.error(err);
                list.innerHTML = '<p style="color: var(--text-danger); font-size: 0.9rem;">Failed to load schedule.</p>';
            }
        }

        async function registerCourse(courseId) {
            try {
                const res = await fetch('api/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'register_course', course_id: courseId })
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    showNotification(data.message, 'success');
                    loadMySchedule(); // Refresh schedule
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (err) {
                showNotification('An error occurred while registering', 'error');
            }
        }

        async function dropCourse(courseId) {
            if (!confirm('Are you sure you want to drop this course?')) return;
            
            try {
                const res = await fetch('api/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'drop_course', course_id: courseId })
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    showNotification(data.message, 'success');
                    loadMySchedule(); // Refresh schedule
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (err) {
                showNotification('An error occurred while dropping the course', 'error');
            }
        }
    </script>
</body>
</html>
