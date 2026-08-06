<?php
foreach(glob(__DIR__ . '/app/Policies/*.php') as $file) {
    $content = file_get_contents($file);
    $content = str_replace('(Admin $user', '(User $user', $content);
    file_put_contents($file, $content);
}
echo "Done";
