<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$isLoggedIn = isset($_SESSION['user']);

$message = '';
$messageType = '';

require_once 'apis/tasks.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    $categories = $_POST['categories'] ?? [];

    if (!is_array($categories)) {
        $categories = [$categories];
    }

    $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $completed = isset($_POST['completed']) ? true : false;

    if (empty($title)) {
        $message = "Task title is required.";
        $messageType = "error";
    } elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $title)) {
        $message = "Task title should not contain special characters.";
        $messageType = "error";
    } else {
        $taskData = [
            'title' => $title,
            'description' => $description,
            'category' => json_encode($categories),
            'completed' => $completed
        ];

        if ($dueDate) {
            $taskData['due_date'] = $dueDate;
        }

        if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
            $taskData['user'] = $_SESSION['user']['id'];
        }

        $result = addTask($supabaseUrl, $supabaseKey, $taskData);

        if ($result['error']) {
            $message = "Error adding task: {$result['error']}";
            $messageType = "error";
        } else if ($result['info']['http_code'] >= 200 && $result['info']['http_code'] < 300) {
            $message = "Task added successfully!";
            $messageType = "success";

            $title = $description = $dueDate = '';
            $completed = false;
        } else {
            $responseData = json_decode($result['response']);
            $message = "Error adding task. Status code: {$result['info']['http_code']}";
            if (isset($responseData->message)) {
                $message .= " - {$responseData->message}";
            }
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<?php include 'header.php'; ?>
<style>
        input[type="text"],
        textarea,
        input[type="date"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .checkbox-group {
            margin-top: 10px;
        }
        .checkbox-group label {
            display: inline;
            font-weight: normal;
        }

</style>
<body>
    <?php include 'web_header.php'; ?>
    <div class="small-container" style="margin:60px auto;">
        <h1>Add Task</h1>

        <?php if (!empty($message)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    <?php if ($messageType === 'success'): ?>
                        notyf.success('<?php echo addslashes($message); ?>');
                    <?php else: ?>
                        notyf.error('<?php echo addslashes($message); ?>');
                    <?php endif; ?>
                });
            </script>
        <?php endif; ?>

        <form method="post" action="" id="taskForm" onsubmit="return validateTaskForm()">
            <div class="form-group">
                <label for="title">Task Title *</label>
                <input type="text" id="title" name="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="categories">Categories</label>
                <select id="categories" name="categories[]" multiple="multiple" class="form-control">
                    <?php
                    $allCategories = ["Urgent", "Frontend", "Backend", "UI-UX"];

                    $selectedCategories = $categories ?? [];

                    foreach ($allCategories as $cat) {
                        $selected = in_array($cat, $selectedCategories) ? 'selected' : '';
                        echo "<option value=\"" . htmlspecialchars($cat) . "\" $selected>" . htmlspecialchars($cat) . "</option>";
                    }
                    ?>
                </select>
                <small>Select multiple categories</small>
            </div>

            <!-- <div class="form-group">
                <label for="new_category">Add New Category</label>
                <input type="text" id="new_category" placeholder="Enter a new category">
                <button type="button" onclick="addNewCategory()">Add</button>
            </div> -->

            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" value="<?php echo isset($dueDate) ? htmlspecialchars($dueDate) : ''; ?>">
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="completed" name="completed" <?php echo isset($completed) && $completed ? 'checked' : ''; ?>>
                <label for="completed">Mark as completed</label>
            </div>

            <div class="form-group">
                <button type="submit" name="submit">Add Task</button>
            </div>
        </form>

    </div>

    <script>
        function addNewCategory() {
            const newCategoryInput = document.getElementById('new_category');
            const categoriesSelect = document.getElementById('categories');

            if (newCategoryInput.value.trim() !== '') {
                let exists = false;
                for (let i = 0; i < categoriesSelect.options.length; i++) {
                    if (categoriesSelect.options[i].value === newCategoryInput.value.trim()) {
                        exists = true;
                        categoriesSelect.options[i].selected = true;
                        break;
                    }
                }

                if (!exists) {
                    const newOption = document.createElement('option');
                    newOption.value = newCategoryInput.value.trim();
                    newOption.text = newCategoryInput.value.trim();
                    newOption.selected = true;
                    categoriesSelect.appendChild(newOption);
                }

                newCategoryInput.value = '';
            }
        }

        function validateTaskForm() {
            const title = document.getElementById('title').value.trim();

            if (title === '') {
                notyf.error('Task title is required');
                return false;
            }

            if (!/^[a-zA-Z0-9\s]+$/.test(title)) {
                notyf.error('Task title should not contain special characters');
                return false;
            }

            return true;
        }
    </script>
    <script>
        $(document).ready(function () {
            $('#categories').select2({
                placeholder: 'Select categories',
                allowClear: false
            });
        })
    </script>
<?php include 'footer.php'; ?>
</body>
</html>
