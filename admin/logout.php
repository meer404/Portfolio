<?php
/**
 * Admin Logout Handler
 */

require_once 'auth.php';

Auth::logout();
header('Location: login.php');
exit;
