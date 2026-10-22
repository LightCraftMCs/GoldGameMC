<?php
require_once __DIR__.'/includes/db.php';
$pageTitle='ساخت حساب'; $currentPage=''; $error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $email=trim((string)($_POST['email']??'')); $username=trim((string)($_POST['username']??'')); $gamer=trim((string)($_POST['gamer_username']??'')); $pass=(string)($_POST['password']??''); $confirm=(string)($_POST['confirm_password']??'');
 if(!csrf_valid()) $error='درخواست نامعتبر است؛ صفحه را تازه‌سازی کن.';
 elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>254) $error='ایمیل معتبر وارد کن.';
 elseif(!preg_match('/^[A-Za-z0-9_]{3,24}$/',$username)) $error='یوزرنیم سایت باید ۳ تا ۲۴ حرف انگلیسی، عدد یا زیرخط باشد.';
 elseif(!preg_match('/^[A-Za-z0-9_]{3,16}$/',$gamer)) $error='نام کاربری گیم باید ۳ تا ۱۶ حرف انگلیسی، عدد یا زیرخط باشد.';
 elseif(strlen($pass)<10||strlen($pass)>200) $error='رمز عبور باید بین ۱۰ تا ۲۰۰ کاراکتر باشد.';
 elseif($pass!==$confirm) $error='تکرار رمز عبور مطابقت ندارد.';
 else { try {
   $hash=password_hash($pass, defined('PASSWORD_ARGON2ID')?PASSWORD_ARGON2ID:PASSWORD_DEFAULT);
   $q=db()->prepare('INSERT INTO users(email,username,gamer_username,password_hash) VALUES(?,?,?,?)'); $q->execute([$email,$username,$gamer,$hash]);
   $id=(int)db()->lastInsertId(); session_regenerate_id(true); $_SESSION['user_id']=$id; $_SESSION['username']=$username; $_SESSION['gamer_username']=$gamer; $_SESSION['csrf']=bin2hex(random_bytes(32)); audit_event($id,'register'); header('Location: index.php'); exit;
  } catch(PDOException $ex) { $error=(string)$ex->getCode()==='23000'?'این ایمیل، یوزرنیم یا نام کاربری گیم قبلاً ثبت شده است.':'ثبت‌نام انجام نشد؛ تنظیمات دیتابیس را بررسی کن.'; } }
}
require __DIR__.'/includes/header.php'; ?>
<div class="wrap auth-wrap"><form method="post" class="form-card"><span class="kicker">JOIN GOLDGAME</span><h1>ساخت حساب کاربری</h1><p class="muted">اطلاعاتت را وارد کن تا حساب بسازی.</p><?php if($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><div class="field"><label for="email">ایمیل</label><input id="email" type="email" name="email" autocomplete="email" required maxlength="254" value="<?= e($_POST['email']??'') ?>"></div><div class="field"><label for="username">یوزرنیم سایت</label><input id="username" name="username" autocomplete="username" required minlength="3" maxlength="24" pattern="[A-Za-z0-9_]{3,24}" value="<?= e($_POST['username']??'') ?>"><span class="small">۳ تا ۲۴ حرف انگلیسی، عدد یا _</span></div><div class="field"><label for="gamer">نام کاربری گیم</label><input id="gamer" name="gamer_username" required minlength="3" maxlength="16" pattern="[A-Za-z0-9_]{3,16}" value="<?= e($_POST['gamer_username']??'') ?>"></div><div class="field"><label for="password">رمز عبور (حداقل ۱۰ کاراکتر)</label><input id="password" type="password" name="password" autocomplete="new-password" required minlength="10" maxlength="200"></div><div class="field"><label for="confirm">تکرار رمز عبور</label><input id="confirm" type="password" name="confirm_password" autocomplete="new-password" required minlength="10" maxlength="200"></div><button class="btn primary" type="submit">ساخت حساب</button><p class="small">رمز عبور به‌صورت هش‌شده ذخیره می‌شود. قبل از انتشار عمومی، تأیید ایمیل و بازیابی رمز را نیز پیکربندی کن.</p><p>قبلاً حساب ساخته‌ای؟ <a href="login.php" style="color:#b78100">ورود</a></p></form></div><?php require __DIR__.'/includes/footer.php'; ?>