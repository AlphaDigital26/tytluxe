<?php

return [
    /*
     * Absolute path to the Chrome/Chromium executable.
     * Set CHROME_PATH in your .env file.
     * Required on production servers where Chrome is not on the system PATH.
     */
    'chrome_path' => env('CHROME_PATH', ''),

    /*
     * Absolute path to the Node.js binary.
     * Set NODE_BINARY_PATH in your .env file.
     * Required on production servers using nvm or non-standard Node installs.
     */
    'node_binary' => env('NODE_BINARY_PATH', ''),

    /*
     * Writable directory for Chrome's user data / crash handler.
     * Defaults to a system temp path. Must be writable by the web server user (www-data).
     */
    'user_data_dir' => env('CHROME_USER_DATA_DIR', '/tmp/chrome-userdata'),
];
