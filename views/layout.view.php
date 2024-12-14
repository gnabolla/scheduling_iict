<?php
// views/layout.view.php

if (!isset($title)) {
    $title = 'My Application';
}
require "partials/head.php";

// If on auth pages, don't include sidebar and nav
if (in_array($view, ['views/login.view.php', 'views/signup.view.php'])) {
    require $view;
} else {
    echo '<div class="wrapper">';
    require "partials/side.php";
    echo '<div id="body" class="active">';
    require "partials/nav.php";
    require $view;
    echo '</div></div>';
}

require "partials/foot.php";
?>
