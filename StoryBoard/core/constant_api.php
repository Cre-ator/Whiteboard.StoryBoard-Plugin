<?php
define( 'STORYBOARD_PLUGIN_URL', config_get_global( 'path' ) . 'plugins/' . plugin_get_current() . '/' );
define( 'STORYBOARD_PLUGIN_URI', config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR );
define( 'STORYBOARD_CORE_URI', STORYBOARD_PLUGIN_URI . 'core/' );
define( 'STORYBOARD_FILES_URI', STORYBOARD_PLUGIN_URL . 'files/' );
define( 'PLUGINS_STORYBOARD_THRESHOLD_LEVEL_DEFAULT', ADMINISTRATOR );