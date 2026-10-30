<?php
require_once __DIR__ . '/config/bootstrap.php';
if (is_logged_in()) {
    redirect(role_home(current_user()['rol']));
}
redirect('login.php');
