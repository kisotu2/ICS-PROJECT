<?php
session_start();
require_once '../db.php';


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Available Interview Slots</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        #calendar {
            max-width: 900px;
            margin: 40px auto;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
    </style>
</head>
<body>

<div style="text-align:center;">
    <h2>Available Interview Slots</h2>
</div>
<div id="calendar"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        slotMinTime: "08:00:00",
        slotMaxTime: "18:00:00",
        allDaySlot: false,
        selectable: false,
        editable: false,

        events: {
            url: 'fetch_slots_available.php',
            method: 'GET'
        },

        eventClick: function (info) {
            if (confirm('Do you want to book this interview slot?')) {
                $.post('book_slot_ajax.php', {
                    slot_id: info.event.id,
                    jobseeker_id: <?= json_encode($jobseekerId) ?>
                }, function (response) {
                    if (response.success) {
                        alert('Interview slot booked!');
                        calendar.refetchEvents();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }, 'json').fail(function () {
                    alert('Server error. Please try again later.');
                });
            }
        }
    });

    calendar.render();
});
</script>

<a href="jobseeker_dashboard.php">Back to Dashboard</a>
</body>
</html>
