<?php

require_once __DIR__ . '/../../backend/classes/UsersClass.php';
$usersObj = new UsersClass();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $allowedRoles    = ['Admin', 'Customer'];
    $allowedStatuses = ['Active', 'Inactive'];

    $action   = $_POST['action'] ?? '';
    $id       = (int)($_POST['id'] ?? 0);
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = $_POST['role'] ?? '';
    $status   = $_POST['status'] ?? '';

    $respond = function (bool $ok, string $msg = '') {
        echo json_encode(['success' => $ok, 'message' => $msg]);
        exit;
    };
    
    try {
        switch ($action) {
            case 'add':
                if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $respond(false, 'Please enter a valid name and email.');
                }
                if (!in_array($role, $allowedRoles, true) || !in_array($status, $allowedStatuses, true)) {
                    $respond(false, 'Invalid role or status.');
                }
                if (strlen($password) < 6) {
                    $respond(false, 'Password must be at least 6 characters.');
                }
                if ($password !== $confirm) {
                    $respond(false, 'Passwords do not match!');
                }
                if ($usersObj->emailExists($email)) {
                    $respond(false, 'This email is already used.');
                }
                $ok = $usersObj->addUser($name, $email, $password, $role, $status);
                $respond($ok, $ok ? 'User added.' : 'Could not add user.');
                break;

                 case 'update':
                if ($id <= 0 || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $respond(false, 'Please enter a valid name and email.');
                }
                if (!in_array($role, $allowedRoles, true) || !in_array($status, $allowedStatuses, true)) {
                    $respond(false, 'Invalid role or status.');
                }
                if ($password !== '') {
                    if (strlen($password) < 8) {
                        $respond(false, 'Password must be at least 8 characters.');
                    }
                    if ($password !== $confirm) {
                        $respond(false, 'Passwords do not match!');
                    }
                }
                if ($usersObj->emailExists($email, $id)) {
                    $respond(false, 'This email is already used.');
                }
                $ok = $usersObj->updateUser($id, $name, $email, $role, $status, $password);
                $respond($ok, $ok ? 'User updated.' : 'Could not update user.');
                break;

                case 'status':
                if ($id <= 0 || !in_array($status, $allowedStatuses, true)) {
                    $respond(false, 'Invalid request.');
                }
                $ok = $usersObj->updateStatus($id, $status);
                $respond($ok, $ok ? 'Status updated.' : 'Could not update status.');
                break;

            case 'delete':
                if ($id <= 0) {
                    $respond(false, 'Invalid request.');
                }
                $ok = $usersObj->deleteUser($id);
                $respond($ok, $ok ? 'User deleted.' : 'User not found.');
                break;
                
            default:
                $respond(false, 'Unknown action.');
                }
    }
    catch (Throwable $e) {
        $respond(false, 'Server error, please try again.');
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management - Admin Dashboard</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link  rel="stylesheet" href="../css/admin-users.css">
  <style>
    .form-message {
      display: none;
      background-color: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: var(--color-error);
      padding: 0.65rem 1rem;
      border-radius: var(--radius-md);
      font-size: 0.85rem;
      font-weight: 600;
      margin-top: 1rem;
    }
    .form-message.show { display: block; }
    #pageMessage { margin: 0 0 1rem 0; }
  </style>
</head>
<body>

  <aside class="admin-sidebar">
   

  </aside>

  
  <main class="admin-main">
    
    <header class="top-bar">
      
    </header>


    <section class="metrics-grid">
      <div class="metric-card">
        <div>
          <div class="metric-title">TOTAL CUSTOMERS</div>
          <div class="metric-value"><?= number_format($stats['total_customers']) ?></div>
          <div class="metric-subtext green">
            <i class="fa-solid fa-arrow-up"></i> +<?= number_format($stats['new_this_month']) ?> <span class="muted">this month</span>
          </div>
        </div>
        <div class="metric-icon-box green">
          <i class="fa-solid fa-users"></i>
        </div>
      </div>

      <div class="metric-card">
        <div>
          <div class="metric-title">ACTIVE CUSTOMERS</div>
          <div class="metric-value"><?= number_format($stats['active_customers']) ?></div>
          <div class="metric-subtext green">
            <i class="fa-solid fa-circle" style="font-size: 0.5rem; vertical-align: middle;"></i> <?= $stats['active_rate'] ?>% <span class="muted">active rate</span>
          </div>
        </div>
        <div class="metric-icon-box blue">
          <i class="fa-solid fa-user-check"></i>
        </div>
      </div>

      <div class="metric-card">
        <div>
          <div class="metric-title">TOTAL REVENUE SPENT</div>
          <div class="metric-value">JD <?= number_format($stats['revenue_total']) ?></div>
          <div class="metric-subtext green">
            <i class="fa-solid fa-arrow-<?= $growth < 0 ? 'down' : 'up' ?>"></i> <?= $growth >= 0 ? '+' : '' ?><?= $growth ?>% <span class="muted">vs last month</span>
          </div>
        </div>
        <div class="metric-icon-box orange">
          <i class="fa-solid fa-wallet"></i>
        </div>
      </div>
    </section>

    <section class="table-container">

      <div id="pageMessage" class="form-message"></div>

      <div class="table-header-actions">
        <!-- Top Row: Search & Add User Button -->
        <div class="top-controls-row">
          <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" class="search-input" placeholder="Search by name, email..." onkeyup="filterAndSortUsers()">
          </div>

          <button class="btn-primary" onclick="openAddUserModal()">
            <i class="fa-solid fa-plus"></i> Add New User
          </button>
        </div>

      
        <div class="filters-row">
          <select id="filterRole" class="select-filter" onchange="filterAndSortUsers()">
            <option value="">All Roles</option>
            <option value="Admin">Admin</option>
            <option value="Customer">Customer</option>
          </select>

          <select id="filterStatus" class="select-filter" onchange="filterAndSortUsers()">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>

          <select id="sortSpent" class="select-filter" onchange="filterAndSortUsers()">
            <option value="">Sort by Spent</option>
            <option value="spent-desc">Spent: High to Low</option>
            <option value="spent-asc">Spent: Low to High</option>
          </select>

          <select id="sortOrders" class="select-filter" onchange="filterAndSortUsers()">
            <option value="">Sort by Orders</option>
            <option value="orders-desc">Orders: High to Low</option>
            <option value="orders-asc">Orders: Low to High</option>
          </select>
        </div>
      </div>


      <table class="data-table" id="usersTable">
        <thead>
          <tr>
            <th>User</th>
            <th>Role</th>
            <th>Status</th>
            <th>Orders</th>
            <th>Total Spent</th>
            <th>Joined Date</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody id="usersTbody">
          <?php foreach ($users as $u): ?>
          <tr data-id="<?= (int)$u['id'] ?>" data-name="<?= e(mb_strtolower($u['name'])) ?>" data-email="<?= e(mb_strtolower($u['email'])) ?>" data-role="<?= e($u['role']) ?>" data-status="<?= e($u['status']) ?>" data-spent="<?= $u['total_spent'] ?>" data-orders="<?= $u['orders_count'] ?>">
            <td>
              <div class="user-info-cell">
                <div class="user-avatar">
                  <?= e(mb_strtoupper(mb_substr($u['name'], 0, 1))) ?>
                </div>
                <div>
                  <span class="user-name"><?= e($u['name']) ?></span>
                  <span class="user-email"><?= e($u['email']) ?></span>
                </div>
              </div>
            </td>
            <td>
              <span class="badge <?= $u['role'] === 'Admin' ? 'badge-role-admin' : 'badge-role-customer' ?>">
                <?= e($u['role']) ?>
              </span>
            </td>
            <td>
              <span class="badge status-badge <?= $u['status'] === 'Active' ? 'badge-status-active' : 'badge-status-inactive' ?>">
                <?= e($u['status']) ?>
              </span>
            </td>
            <td><?= $u['orders_count'] ?></td>
            <td><strong>JD <?= number_format($u['total_spent'], 2) ?></strong></td>
            <td><?= e($u['created_at']) ?></td>
            <td style="text-align: right;">
              <button class="action-btn" title="Edit User" onclick="openEditUserModal(this)"><i class="fa-solid fa-pen-to-square"></i></button>
              <?php if ($u['status'] === 'Active'): ?>
                <button class="action-btn ban" title="Ban User" onclick="toggleUserStatus(this, 'Inactive')">
                  <i class="fa-solid fa-ban"></i>
                </button>
              <?php else: ?>
                <button class="action-btn unban" title="Activate User" onclick="toggleUserStatus(this, 'Active')">
                  <i class="fa-solid fa-check-circle"></i>
                </button>
              <?php endif; ?>
              <button class="action-btn delete" title="Delete User" onclick="deleteUser(this)"><i class="fa-solid fa-trash"></i></button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    </section>

  </main>


  <div id="addUserModal" class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header">
        <h3>Add New User</h3>
        <button class="modal-close" onclick="closeAddUserModal()"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form id="addUserForm" onsubmit="handleAddNewUser(event)">
        <div class="form-group">
          <label for="newUserName">Full Name</label>
          <input type="text" id="newUserName" class="form-control" placeholder="e.g. Ali Ahmed" required>
        </div>
        <div class="form-group">
          <label for="newUserEmail">Email Address</label>
          <input type="email" id="newUserEmail" class="form-control" placeholder="e.g. ali@example.com" required>
        </div>
        <div class="form-group">
          <label for="newUserPassword">Password</label>
          <input type="password" id="newUserPassword" class="form-control" placeholder="********" required>
        </div>
        <div class="form-group">
          <label for="newUserConfirmPassword">Confirm Password</label>
          <input type="password" id="newUserConfirmPassword" class="form-control" placeholder="********" required>
        </div>
        <div class="form-group">
          <label for="newUserRole">Role</label>
          <select id="newUserRole" class="form-control" required>
            <option value="Customer">Customer</option>
            <option value="Admin">Admin</option>
          </select>
        </div>
        <div class="form-group">
          <label for="newUserStatus">Status</label>
          <select id="newUserStatus" class="form-control" required>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
        <div id="addUserError" class="form-message"></div>
        <div class="modal-actions">
          <button type="button" class="btn-secondary" onclick="closeAddUserModal()">Cancel</button>
          <button type="submit" class="btn-primary">Save User</button>
        </div>
      </form>
    </div>
  </div>

  <div id="editUserModal" class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header">
        <h3>Edit User</h3>
        <button class="modal-close" onclick="closeEditUserModal()"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form id="editUserForm" onsubmit="handleEditUser(event)">
        <input type="hidden" id="editUserId">
        <div class="form-group">
          <label for="editUserName">Full Name</label>
          <input type="text" id="editUserName" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="editUserEmail">Email Address</label>
          <input type="email" id="editUserEmail" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="editUserPassword">New Password (leave empty to keep current)</label>
          <input type="password" id="editUserPassword" class="form-control" placeholder="********">
        </div>
        <div class="form-group">
          <label for="editUserConfirmPassword">Confirm New Password</label>
          <input type="password" id="editUserConfirmPassword" class="form-control" placeholder="********">
        </div>
        <div class="form-group">
          <label for="editUserRole">Role</label>
          <select id="editUserRole" class="form-control" required>
            <option value="Customer">Customer</option>
            <option value="Admin">Admin</option>
          </select>
        </div>
        <div class="form-group">
          <label for="editUserStatus">Status</label>
          <select id="editUserStatus" class="form-control" required>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
        <div id="editUserError" class="form-message"></div>
        <div class="modal-actions">
          <button type="button" class="btn-secondary" onclick="closeEditUserModal()">Cancel</button>
          <button type="submit" class="btn-primary">Update User</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function showMessage(id, text) {
      const box = document.getElementById(id);
      box.textContent = text;
      box.classList.add('show');
    }

    function hideMessage(id) {
      const box = document.getElementById(id);
      box.textContent = '';
      box.classList.remove('show');
    }

    function openAddUserModal() {
      hideMessage('addUserError');
      document.getElementById('addUserModal').classList.add('active');
    }

    function closeAddUserModal() {
      hideMessage('addUserError');
      document.getElementById('addUserModal').classList.remove('active');
      document.getElementById('addUserForm').reset();
    }

    async function sendRequest(data) {
      try {
        const res = await fetch('users.php', { method: 'POST', body: new URLSearchParams(data) });
        return await res.json();
      } catch (err) {
        return { success: false, message: 'Network or server error.' };
      }
    }

    async function handleAddNewUser(event) {
      event.preventDefault();
      hideMessage('addUserError');

      const password = document.getElementById('newUserPassword').value;
      const confirmPassword = document.getElementById('newUserConfirmPassword').value;

     
      if (password !== confirmPassword) {
        showMessage('addUserError', 'Passwords do not match!');
        return;
      }

      const result = await sendRequest({
        action: 'add',
        name: document.getElementById('newUserName').value,
        email: document.getElementById('newUserEmail').value,
        password: password,
        confirm_password: confirmPassword,
        role: document.getElementById('newUserRole').value,
        status: document.getElementById('newUserStatus').value
      });

      if (!result.success) {
        showMessage('addUserError', result.message);
        return;
      }

      closeAddUserModal();
      window.location.reload(); 
    }

    function openEditUserModal(button) {
      hideMessage('editUserError');
      const row = button.closest('tr');
      document.getElementById('editUserId').value = row.getAttribute('data-id');
      document.getElementById('editUserName').value = row.querySelector('.user-name').textContent.trim();
      document.getElementById('editUserEmail').value = row.querySelector('.user-email').textContent.trim();
      document.getElementById('editUserRole').value = row.getAttribute('data-role');
      document.getElementById('editUserStatus').value = row.getAttribute('data-status');
      document.getElementById('editUserPassword').value = '';
      document.getElementById('editUserConfirmPassword').value = '';
      document.getElementById('editUserModal').classList.add('active');
    }

    function closeEditUserModal() {
      hideMessage('editUserError');
      document.getElementById('editUserModal').classList.remove('active');
      document.getElementById('editUserForm').reset();
    }

    async function handleEditUser(event) {
      event.preventDefault();
      hideMessage('editUserError');

      const password = document.getElementById('editUserPassword').value;
      const confirmPassword = document.getElementById('editUserConfirmPassword').value;

      if (password !== confirmPassword) {
        showMessage('editUserError', 'Passwords do not match!');
        return;
      }

      const result = await sendRequest({
        action: 'update',
        id: document.getElementById('editUserId').value,
        name: document.getElementById('editUserName').value,
        email: document.getElementById('editUserEmail').value,
        password: password,
        confirm_password: confirmPassword,
        role: document.getElementById('editUserRole').value,
        status: document.getElementById('editUserStatus').value
      });

      if (!result.success) {
        showMessage('editUserError', result.message);
        return;
      }

      closeEditUserModal();
      window.location.reload();
    }

    async function deleteUser(button) {
      const row = button.closest('tr');
      if (!confirm('Are you sure you want to delete this user?')) return;
      hideMessage('pageMessage');

      const result = await sendRequest({ action: 'delete', id: row.getAttribute('data-id') });

      if (!result.success) {
        showMessage('pageMessage', result.message);
        return;
      }
      row.remove();
    }

    function filterAndSortUsers() {
      const searchVal = document.getElementById('searchInput').value.toLowerCase();
      const roleVal = document.getElementById('filterRole').value;
      const statusVal = document.getElementById('filterStatus').value;
      
      const spentSort = document.getElementById('sortSpent').value;
      const ordersSort = document.getElementById('sortOrders').value;

      const tbody = document.getElementById('usersTbody');
      const rows = Array.from(tbody.querySelectorAll('tr'));

      rows.forEach(row => {
        const name = row.getAttribute('data-name');
        const email = row.getAttribute('data-email');
        const role = row.getAttribute('data-role');
        const status = row.getAttribute('data-status');

        const matchesSearch = name.includes(searchVal) || email.includes(searchVal);
        const matchesRole = !roleVal || role === roleVal;
        const matchesStatus = !statusVal || status === statusVal;

        if (matchesSearch && matchesRole && matchesStatus) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });

      rows.sort((a, b) => {
        const spentA = parseFloat(a.getAttribute('data-spent')) || 0;
        const spentB = parseFloat(b.getAttribute('data-spent')) || 0;
        const ordersA = parseInt(a.getAttribute('data-orders')) || 0;
        const ordersB = parseInt(b.getAttribute('data-orders')) || 0;

        if (spentSort === 'spent-desc') return spentB - spentA;
        if (spentSort === 'spent-asc') return spentA - spentB;

        if (ordersSort === 'orders-desc') return ordersB - ordersA;
        if (ordersSort === 'orders-asc') return ordersA - ordersB;

        return 0;
      });

      rows.forEach(row => tbody.appendChild(row));
    }

    async function toggleUserStatus(button, newStatus) {
      const row = button.closest('tr');
      const statusBadge = row.querySelector('.status-badge');
      hideMessage('pageMessage');

      const result = await sendRequest({ action: 'status', id: row.getAttribute('data-id'), status: newStatus });

      if (!result.success) {
        showMessage('pageMessage', result.message);
        return;
      }

      row.setAttribute('data-status', newStatus);

      if (newStatus === 'Inactive') {
        statusBadge.className = 'badge status-badge badge-status-inactive';
        statusBadge.textContent = 'Inactive';

        button.className = 'action-btn unban';
        button.title = 'Activate User';
        button.innerHTML = '<i class="fa-solid fa-check-circle"></i>';
        button.setAttribute('onclick', "toggleUserStatus(this, 'Active')");
      } else {
        statusBadge.className = 'badge status-badge badge-status-active';
        statusBadge.textContent = 'Active';

        button.className = 'action-btn ban';
        button.title = 'Ban User';
        button.innerHTML = '<i class="fa-solid fa-ban"></i>';
        button.setAttribute('onclick', "toggleUserStatus(this, 'Inactive')");
      }
    }
  </script>

</body>
</html>