<?php

register_deactivation_hook(GS_INDEX, function () use ($options) {
    include plugin_dir_path(GS_INDEX) . 'uninstall.php';
});
