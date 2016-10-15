<?php
require '/opt/vendor/autoload.php'; try {

    Srvlets\Engine::init(
        new Srvlets\Server(
            new Srvlets\Bootstrapper\Main,
            $port    = 8000,
            $threads = 1,
            $workers = 64
        ),
        new Srvlets\Process('/usr/sbin/nginx')
    );

} catch (Throwable $throwable) {

    // Something went horribly wrong...
}
