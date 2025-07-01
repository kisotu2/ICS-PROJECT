<?php
session_start();
require_once '../db.php';

// Check organisation login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisation') {
    header("Location: ../login.php");
    exit();
}

$orgId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Interview Slots</title>

    <!-- FullCalendar v6 global build (includes timeGrid) -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        #calendar {
            max-width: 900px;
            margin: 40px auto;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            font-weight: bold;
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<h2>Manage Available Interview Slots</h2>
<div id="calendar"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        slotMinTime: "08:00:00",
        slotMaxTime: "18:00:00",
        allDaySlot: false,
        selectable: true,
        editable: true,

        events: {
            url: 'fetch_slots_org.php',
            method: 'POST',
            extraParams: {
                organisation_id: <?= json_encode($orgId) ?>
            }
        },

        select: function (info) {
            let title = prompt('Enter slot title (e.g. Interview Slot)');
            if (title) {
                $.post('add_slot_ajax.php', {
                    organisation_id: <?= json_encode($orgId) ?>,
                    slot_date: info.startStr.substring(0, 10),
                    slot_start: info.startStr.substring(11, 19),
                    slot_end: info.endStr.substring(11, 19),
                    title: title
                }, function (response) {
                    if (response.success) {
                        calendar.refetchEvents();
                        alert('Slot added successfully.');
                    } else {
                        alert('Error: ' + response.message);
                    }
                }, 'json');
            }
            calendar.unselect();
        },

        eventClick: function (info) {
            if (info.event.extendedProps.isBooked) {
                alert("This slot is already booked and cannot be edited or deleted.");
                return;
            }

            let choice = prompt("Type 'delete' to remove this slot or enter a new title to edit:", info.event.title);
            if (choice === null) return;

            if (choice.toLowerCase() === 'delete') {
                if (confirm('Delete this slot?')) {
                    $.post('delete_slot_ajax.php', {
                        slot_id: info.event.id
                    }, function (response) {
                        if (response.success) {
                            calendar.refetchEvents();
                            alert('Slot deleted.');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    }, 'json');
                }
            } else {
                alert('Editing slot titles is not implemented yet.');
            }
        },

        eventDrop: function (info) {
            if (info.event.extendedProps.isBooked) {
                alert("Cannot move booked slots.");
                info.revert();
                return;
            }

            $.post('update_slot_ajax.php', {
                slot_id: info.event.id,
                slot_date: info.event.startStr.substring(0, 10),
                slot_start: info.event.startStr.substring(11, 19),
                slot_end: info.event.endStr.substring(11, 19)
            }, function (response) {
                if (!response.success) {
                    alert('Error: ' + response.message);
                    info.revert();
                }
            }, 'json');
        }
    });

    calendar.render();
});

</script>

<a href="organisation_dashboard.php" class="back-link">← Back to Dashboard</a>

</body>
</html>
