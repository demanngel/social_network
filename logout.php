<?php
session_start();
session_unset();
session_destroy();

header('Location: loginForm.php?message=logged_out');
exit();