<?php
// Copy to config.php, keep it outside public version control, and use a least-privilege DB user.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'goldgame');
define('DB_USER', 'goldgame_user');
define('DB_PASS', 'REPLACE_WITH_A_LONG_RANDOM_PASSWORD');
// Random secret used only to key login-rate-limit identifiers. Replace before deployment.
define('AUTH_PEPPER', 'REPLACE_WITH_A_RANDOM_SECRET_OF_AT_LEAST_32_CHARACTERS');
