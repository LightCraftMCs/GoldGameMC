<?php
require_once __DIR__.'/includes/db.php'; $pageTitle='ورود به حساب'; $currentPage=''; $error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $login=trim((string)($_POST['login']??'')); $pass=(string)($_POST['password']??'');
 if(!csrf_valid()) $error='درخواست نامعتبر است؛ صفحه را تازه‌سازی کن.';
 elseif(strlen($login)>254||$login==='') $error='اطلاعات ورود درست نیست.';
 else { try { $key=login_key($login); if(login_is_limited($key)) { $error='تلاش‌های ورود بیش از حد است؛ ۱۵ دقیقه بعد دوباره تلاش کن.'; }
   else { $q=db()->prepare('SELECT id,username,gamer_username,password_hash FROM users WHERE email=? OR username=? LIMIT 1'); $q->execute([$login,$login]); $u=$q->fetch();
    if($u && password_verify($pass,$u['password_hash'])) { clear_login_failures($key); if(password_needs_rehash($u['password_hash'],defined('PASSWORD_ARGON2ID')?PASSWORD_ARGON2ID:PASSWORD_DEFAULT)){ $h=password_hash($pass,defined('PASSWORD_ARGON2ID')?PASSWORD_ARGON2ID:PASSWORD_DEFAULT); db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$h,$u['id']]); } session_regenerate_id(true); $_SESSION['user_id']=(int)$u['id']; $_SESSION['username']=$u['username']; $_SESSION['gamer_username']=$u['gamer_username']; $_SESSION['csrf']=bin2hex(random_bytes(32)); audit_event((int)$u['id'],'login'); header('Location: index.php'); exit; }
    else { record_login_failure($key); $error='اطلاعات ورود درست نیست.'; }
   }
  } catch(Throwable $ex) { $error='ورود انجام نشد؛ config.php و دیتابیس را بررسی کن.'; }
 }
}
require __DIR__.'/includes/header.php'; ?>
<div class="wrap auth-wrap"><form method="post" class="form-card"><span class="kicker">WELCOME BACK</span><h1>ورود به حساب</h1><p class="muted">با ایمیل یا یوزرنیم وارد شو.</p><?php if($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><div class="field"><label for="login">ایمیل یا یوزرنیم</label><input id="login" name="login" autocomplete="username" required maxlength="254" value="<?= e($_POST['login']??'') ?>"></div><div class="field"><label for="password">رمز عبور</label><input id="password" type="password" name="password" autocomplete="current-password" required maxlength="200"></div><button class="btn primary" type="submit">ورود امن</button><p>حساب نداری؟ <a href="register.php" style="color:#b78100">ثبت‌نام کن</a></p></form></div><?php require __DIR__.'/includes/footer.php'; ?>