<?php
declare(strict_types=1);


require_once __DIR__ . '/../../backend/classes/UsersClass.php';
require_once __DIR__ . '/../layout.php';

$loginUrl = '../../backend/auth/login.php';

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) {
    header('Location: ' . $loginUrl);
    exit;
}

if (($_SESSION['user_role'] ?? '') === 'customer') {
    header('Location: ../User/userprofile.php');
    exit;
}

$usersObj = new UsersClass();




if (empty($_SESSION['csrf_admin_profile'])) {
    $_SESSION['csrf_admin_profile'] = bin2hex(random_bytes(32));
}

$flash  = ['type' => '', 'msg' => ''];
$errors = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['csrf'] ?? '');

    if (!hash_equals($_SESSION['csrf_admin_profile'], $token)) {
        $flash = ['type' => 'error', 'msg' => 'Invalid request. Please try again.'];
    } else {
        try {
            if (isset($_POST['update_profile'])) {
                $name    = trim((string)($_POST['name'] ?? ''));
                $email   = trim((string)($_POST['email'] ?? ''));
                $current = $usersObj->getProfile($userId);
                $address = (string)($current['address_line1'] ?? '');   // keep existing address

                if ($name === '' || mb_strlen($name) > 100) {
                    $errors['name'] = 'Enter a valid name (max 100 characters).';
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
                    $errors['email'] = 'Enter a valid email address.';
                } elseif ($usersObj->emailExists($email, $userId)) {
                    $errors['email'] = 'This email is already used by another account.';
                }

                if (!$errors) {
                    if ($usersObj->updateProfile($userId, $name, $email, $address)) {
                        $_SESSION['user_name']  = $name;
                        $_SESSION['admin_name'] = $name;
                        $_SESSION['email']      = $email;
                        $flash = ['type' => 'success', 'msg' => 'Profile updated successfully.'];
                    } else {
                        $flash = ['type' => 'error', 'msg' => 'Could not update your profile.'];
                    }
                } else {
                    $flash = ['type' => 'error', 'msg' => 'Please fix the errors below.'];
                }
            }

            if (isset($_POST['change_password'])) {
                $currentPw = (string)($_POST['current_password'] ?? '');
                $new       = (string)($_POST['new_password'] ?? '');
                $confirm   = (string)($_POST['confirm_password'] ?? '');

                if ($currentPw === '') {
                    $errors['current_password'] = 'Enter your current password.';
                }
                if (strlen($new) < 8) {
                    $errors['new_password'] = 'New password must be at least 8 characters.';
                }
                if ($new !== $confirm) {
                    $errors['confirm_password'] = 'Passwords do not match.';
                }

                if (!$errors) {
                    if ($usersObj->changePassword($userId, $currentPw, $new)) {
                        session_regenerate_id(true);
                        $flash = ['type' => 'success', 'msg' => 'Password changed successfully.'];
                    } else {
                        $errors['current_password'] = 'Current password is incorrect.';
                        $flash = ['type' => 'error', 'msg' => 'Could not change your password.'];
                    }
                } else {
                    $flash = ['type' => 'error', 'msg' => 'Please fix the errors below.'];
                }
            }
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $flash = ['type' => 'error', 'msg' => 'Server error, please try again.'];
        }
    }
}


$profile = $usersObj->getProfile($userId);
if (!$profile) {
    header('Location: ' . $loginUrl);
    exit;
}


$val = static function (string $key, string $fallback) use ($errors): string {
    return isset($_POST[$key]) && !empty($errors) ? (string)$_POST[$key] : $fallback;
};
$fName  = $val('name', (string)$profile['name']);
$fEmail = $val('email', (string)$profile['email']);

$extraHead = '
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin-users.css">
  <style>
    .pf-top{display:flex;align-items:center;gap:1.25rem;background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-card);padding:1.5rem;margin-bottom:1.25rem;flex-wrap:wrap}
    .pf-avatar{width:68px;height:68px;border-radius:50%;background:var(--color-primary);color:var(--color-text-inverse);display:grid;place-items:center;font-size:1.75rem;font-weight:700;flex-shrink:0}
    .pf-top h2{color:var(--color-text);font-size:1.35rem;margin:0}
    .pf-top p{margin:.15rem 0 0;color:var(--color-text-secondary)}
    .pf-tags{margin-left:auto;display:flex;gap:.5rem;flex-wrap:wrap}
    .pf-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1.25rem}
    .pf-panel{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-card);padding:1.5rem}
    .pf-panel h3{color:var(--color-text);margin:0 0 .75rem;font-size:1.1rem}
    .pf-panel .btn-primary{margin-top:1.25rem}
    .pf-alert{padding:.75rem 1rem;border-radius:var(--radius-md);margin-bottom:1.25rem;font-weight:600;font-size:.9rem;border:1px solid var(--color-border)}
    .pf-alert.success{background:rgba(163,230,53,.1);border-color:var(--color-border-glow);color:var(--color-success)}
    .pf-alert.error{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:var(--color-error)}
    .pf-err{display:block;color:var(--color-error);font-size:.8rem;margin-top:.35rem}
  </style>
';

page_start('My profile', 'profile.php', $extraHead);

heading('Account', 'My Profile', 'Manage your admin account details and password.');
?>

<?php if ($flash['msg'] !== ''): ?>
  <div class="pf-alert <?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></div>
<?php endif; ?>

<section class="pf-top">
  <div class="pf-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($profile['name'], 0, 1))) ?></div>
  <div>
    <h2><?= e($profile['name']) ?></h2>
    <p><?= e($profile['email']) ?> · Member since <?= e(substr((string)$profile['created_at'], 0, 10)) ?></p>
  </div>
  <div class="pf-tags">
    <span class="badge badge-role-admin"><?= e(ucfirst((string)$profile['role'])) ?></span>
  </div>
</section>

<div class="pf-grid">

 
  <form class="pf-panel" method="post" novalidate>
    <h3>Personal information</h3>
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf_admin_profile']) ?>">

    <div class="form-group">
      <label for="pfName">Full name</label>
      <input id="pfName" name="name" type="text" class="form-control" maxlength="100" required value="<?= e($fName) ?>">
      <?php if (isset($errors['name'])): ?><span class="pf-err"><?= e($errors['name']) ?></span><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="pfEmail">Email address</label>
      <input id="pfEmail" name="email" type="email" class="form-control" maxlength="150" required value="<?= e($fEmail) ?>">
      <?php if (isset($errors['email'])): ?><span class="pf-err"><?= e($errors['email']) ?></span><?php endif; ?>
    </div>

    <button class="btn-primary" type="submit" name="update_profile" value="1">Save changes</button>
  </form>

  <form class="pf-panel" method="post" autocomplete="off" novalidate>
    <h3>Change password</h3>
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf_admin_profile']) ?>">

    <div class="form-group">
      <label for="pfCurrent">Current password</label>
      <input id="pfCurrent" name="current_password" type="password" class="form-control" autocomplete="current-password" required>
      <?php if (isset($errors['current_password'])): ?><span class="pf-err"><?= e($errors['current_password']) ?></span><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="pfNew">New password</label>
      <input id="pfNew" name="new_password" type="password" class="form-control" minlength="8" autocomplete="new-password" required>
      <?php if (isset($errors['new_password'])): ?><span class="pf-err"><?= e($errors['new_password']) ?></span><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="pfConfirm">Confirm new password</label>
      <input id="pfConfirm" name="confirm_password" type="password" class="form-control" minlength="8" autocomplete="new-password" required>
      <?php if (isset($errors['confirm_password'])): ?><span class="pf-err"><?= e($errors['confirm_password']) ?></span><?php endif; ?>
    </div>

    <button class="btn-primary" type="submit" name="change_password" value="1">Update password</button>
  </form>

</div>

<?php page_end(); ?>