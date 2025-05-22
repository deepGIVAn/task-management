<?php

require_once 'apis/tasks.php';

$categoryFilter = $_GET['category'] ?? '';
$dateFilter = $_GET['due_date'] ?? '';
$searchQuery = $_GET['search'] ?? '';

$message = '';
$messageType = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] == 'delete') {
        $message = "Task deleted successfully!";
        $messageType = "success";
    }
} elseif (isset($_GET['error'])) {
    $messageType = "error";

    switch ($_GET['error']) {
        case 'fetch':
            $message = "Error fetching task details.";
            break;
        case 'unauthorized':
            $message = "You are not authorized to perform this action.";
            break;
        case 'delete':
            $message = "Error deleting task.";
            if (isset($_GET['message'])) {
                $message .= " " . $_GET['message'];
            }
            break;
        case 'notfound':
            $message = "Task not found.";
            break;
        default:
            $message = "An error occurred.";
    }

    if (isset($_GET['code'])) {
        $message .= " (Code: " . $_GET['code'] . ")";
    }
}

// Display toast notification if there's a message
if (!empty($message)) {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            ' . ($messageType === 'success' ? 'notyf.success' : 'notyf.error') . '("' . addslashes($message) . '");
        });
    </script>';
}

                    // <div class="filter-group">
                    //     <label for="search">Search by Title</label>
                    //     <input type="text" id="search" name="search" value="' . htmlspecialchars($searchQuery) . '" placeholder="Search tasks...">
                    // </div>


echo '
    <div class="">

        <!-- Filter and Search Form -->
        <div class="filters">
            <form method="get" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="category">Filter by Category</label>
                        <select id="category" name="category">
                            <option value="">All Categories</option>
                            <option value="Urgent" ' . ($categoryFilter == 'Urgent' ? 'selected' : '') . '>Urgent</option>
                            <option value="Frontend" ' . ($categoryFilter == 'Frontend' ? 'selected' : '') . '>Frontend</option>
                            <option value="Backend" ' . ($categoryFilter == 'Backend' ? 'selected' : '') . '>Backend</option>
                            <option value="ui_ux" ' . ($categoryFilter == 'ui_ux' ? 'selected' : '') . '>UI-UX</option>
                            ';
echo '                  </select>
                    </div>
                    <div class="filter-group">
                        <label for="due_date">Filter by Due Date</label>
                        <input type="date" id="due_date" name="due_date" value="' . htmlspecialchars($dateFilter) . '">
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary" style="width:150px">Apply Filters</button>
                    <a href="index.php" class="btn btn-secondary">Clear Filters</a>
                </div>
            </form>
        </div>';

$userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;

$result = getAllTasks($supabaseUrl, $supabaseKey, $userId, $categoryFilter, $dateFilter);

if ($result['error']) {
    echo "<div class='error'>
            <h3>Error connecting to Supabase</h3>
            <p>{$result['error']}</p>
          </div>";
} else {
    $status_code = $result['info']['http_code'];
    $response_data = json_decode($result['response']);

    if ($status_code == 200) {
        echo "<h2>All Tasks</h2>";

        $filtered_tasks = [];

        if (is_array($response_data)) {
            foreach ($response_data as $task) {
                if (!empty($categoryFilter)) {
                    if (!isset($task->category) || empty($task->category)) {
                        continue;
                    }

                    $taskCategories = json_decode($task->category, true);
                    if (!is_array($taskCategories) || !in_array($categoryFilter, $taskCategories)) {
                        continue;
                    }
                }

                if (!empty($dateFilter) && (!isset($task->due_date) || substr($task->due_date, 0, 10) != $dateFilter)) {
                    continue;
                }

                if (!empty($searchQuery) && (!isset($task->title) || stripos($task->title, $searchQuery) === false)) {
                    continue;
                }

                $filtered_tasks[] = $task;
            }
        }

        if (count($filtered_tasks) > 0) {
            echo "<div class='task-table-container'>";
            echo "<table id='taskTable' class='display' style='width:100%'>";
            echo "<thead>
                    <tr>
                        <th>Title</th>
                        <th>Categories</th>
                        <th>Description</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                  </thead>";
            echo "<tbody>";

            foreach ($filtered_tasks as $task) {
                $status_class = isset($task->completed) && $task->completed ? 'status-completed' : 'status-pending';
                $status_text = isset($task->completed) && $task->completed ? 'Completed' : 'Pending';
                $isOwner = isset($task->user) && isset($_SESSION['user']) && $task->user == $_SESSION['user']['id'];

                echo "<tr>";

                // Title
                if (isset($task->title)) {
                    echo "<td>" . htmlspecialchars($task->title) . "</td>";
                } else {
                    echo "<td>Task #{$task->id}</td>";
                }

                // Categories
                echo "<td>";
                if (isset($task->category) && !empty($task->category)) {
                    $taskCategories = json_decode($task->category, true);
                    if (is_array($taskCategories)) {
                        foreach ($taskCategories as $cat) {
                            echo "<span class='task-category'>" . htmlspecialchars($cat) . "</span> ";
                        }
                    }
                }
                echo "</td>";

                // Description
                echo "<td>";
                if (isset($task->description) && !empty($task->description)) {
                    // Truncate description if it's too long
                    $desc = htmlspecialchars($task->description);
                    echo (strlen($desc) > 100) ? substr($desc, 0, 100) . "..." : $desc;
                }
                echo "</td>";

                // Owner
                echo "<td>";
                if (isset($task->users) && isset($task->users->username)) {
                    // Check if the task owner is the current user
                    if (isset($_SESSION['user']) && isset($task->user) && $task->user == $_SESSION['user']['id']) {
                        echo "<span class='current-user'>You</span>";
                    } else {
                        echo htmlspecialchars($task->users->username);
                    }
                } else {
                    echo "<span class='text-muted'>Unknown</span>";
                }
                echo "</td>";

                // Status
                echo "<td><span class='status {$status_class}'>{$status_text}</span></td>";

                // Due Date
                echo "<td>";
                if (isset($task->due_date) && !empty($task->due_date)) {
                    // Convert the date to a more readable format
                    $dueDate = new DateTime($task->due_date);
                    echo $dueDate->format('M d, Y'); // Format: Jan 01, 2023
                }
                echo "</td>";

                // Created At
                echo "<td>";
                if (isset($task->created_at) && !empty($task->created_at)) {
                    // Convert the timestamp to a more readable format
                    $createdDate = new DateTime($task->created_at);
                    echo $createdDate->format('M d, Y g:i A'); // Format: Jan 01, 2023 3:45 PM
                }
                echo "</td>";

                // Actions
                echo "<td>";
                if ($isOwner) {
                    echo "<a href='edit_task.php?id={$task->id}' class='action-btn edit-btn'>Edit</a> ";
                    echo "<a href='javascript:void(0)' class='action-btn delete-btn' onclick='confirmAction(\"Are you sure you want to delete this task?\", function() { window.location.href = \"delete_task.php?id={$task->id}\"; })'>Delete</a>";
                }
                echo "</td>";

                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
            echo "</div>";

            echo "<script>
                // Add a custom date sorting plugin for our formatted dates
                $.fn.dataTable.ext.type.order['date-mmm-dd-yyyy-pre'] = function(data) {
                    if (!data) return 0;

                    // Extract date parts from formats like 'Jan 01, 2023' or 'Jan 01, 2023 3:45 PM'
                    const dateParts = data.split(' ');
                    if (dateParts.length < 3) return 0;

                    const months = {
                        'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'May': 4, 'Jun': 5,
                        'Jul': 6, 'Aug': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11
                    };

                    const month = months[dateParts[0]];
                    const day = parseInt(dateParts[1].replace(',', ''));
                    const year = parseInt(dateParts[2]);

                    // Create a date object for comparison
                    return new Date(year, month, day).getTime();
                };

                $(document).ready(function() {
                    $('#taskTable').DataTable({
                        'order': [[5, 'asc']], // Sort by due date by default
                        'pageLength': 10,
                        'responsive': true,
                        'columnDefs': [
                            { 'orderable': false, 'targets': 7 }, // Disable sorting on Actions column
                            {
                                'targets': [5, 6], // Due date and Created at columns
                                'type': 'date-mmm-dd-yyyy' // Use our custom date sorting for these columns
                            }
                        ],
                        // Disable the built-in search if we're using our custom filter
                        'searching': " . (empty($searchQuery) && empty($categoryFilter) && empty($dateFilter) ? 'true' : 'false') . ",
                        // Customize the language
                        'language': {
                            'emptyTable': 'No tasks found. You can <a href=\"add_task.php\">add a new task</a>.',
                            'info': 'Showing _START_ to _END_ of _TOTAL_ tasks',
                            'infoEmpty': 'No tasks available',
                            'lengthMenu': 'Show _MENU_ tasks per page',
                            'zeroRecords': 'No matching tasks found'
                        }
                    });

                    // If we have active filters, add a note about it
                    " . ((!empty($searchQuery) || !empty($categoryFilter) || !empty($dateFilter)) ?
                        "$('.dataTables_wrapper').prepend('<div class=\"filter-notice\">Showing filtered results. <a href=\"index.php\">Clear all filters</a></div>');" : '') . "
                });
            </script>";
        } else {
            echo "<p class='no-tasks'>No tasks found. you can <a href='add_task.php'>add a new task</a>.</p>";
        }
    } else {
        echo "<div class='error'>
                <h3>Error fetching tasks</h3>
                <p>Status code: {$status_code}</p>
                <pre>" . print_r($response_data, true) . "</pre>
              </div>";
    }
}

echo '</div>';