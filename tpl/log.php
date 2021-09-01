# Gravity Scores Log <?=  date('d.m.y - H:i'); ?>

## Metadata

- **URL:** <?= $request_url; ?> 
<?php if ($user_login === null): ?>
- **Logged In:** " <?= (($user_id !== 0) ? 'true' : 'false'); ?>  
- **User Login:** <?= ($user_login == null) ? 'unknown' : $user_login; ?>  
- **User Id:** <?= $user_id; ?>  
<?php endif; ?>

<?php if (!empty($messages['error'])): ?>
<!-- GET -->
<?php if (!empty($_GET)): ?>
### Get
<?php foreach ($_GET as $get_entry_key => $get_entry_value): ?>
- **<?= $get_entry_key; ?>:** <?= $get_entry_value ?>  
<?php endforeach; ?>
<?php endif; ?>

<!-- POST -->
<?php if (!empty($_POST)): ?>
### Post
<?php foreach ($_POST as $post_entry_key => $post_entry_value): ?>
- **<?= $post_entry_key; ?>:** <?= $post_entry_value ?>  
<?php endforeach; ?>
<?php endif; ?>

<!-- COOKIE -->
<?php if (!empty($_COOKIE) && is_admin()): ?>
### Cookie
<?php foreach ($_COOKIE as $cookie_entry_key => $cookie_entry_value): ?>
- **<?= $cookie_entry_key; ?>:** <?= $cookie_entry_value ?>  
<?php endforeach; ?>
<?php endif; ?>

<!-- ERROR -->
## Exceptions and Error Messages
<?php foreach ($messages['error'] as $message): ?>
- <?= $message ?>
<?php endforeach; ?>
<?php endif ?>

<!-- SUCCESS -->
<?php if (!empty($messages['success'])): ?>
## Success Messages
<?php foreach ($messages['success'] as $message): ?>
- <?= $message ?>  
<?php endforeach; ?>
<?php endif ?>

<!-- LOG -->
<?php if (!empty($messages['log'])): ?>
## Log Messages
<?php foreach ($messages['log'] as $message): ?>
- <?= $message ?>  
<?php endforeach; ?>
<?php endif ?>
