<?php

/**
 * Local configuration for auth settings.
 *
 * Copy this file to `auth.local.php` and change its settings as required.
 * `auth.local.php` is ignored by git and safe to use for local and sensitive data like usernames and passwords.
 */

return [
    "jwtSecretPrivateKeyFile" => "././_SSL/jwt_secret_private__dev.pem",
    "jwtSecretPublicKeyFile" => "././_SSL/jwt_secret_public__dev.pem",
];