<?php
/**
 * Created by v.taneev.
 */

return [
    'dadata' => [
        'url' => getenv('DADATA_SERVICE_URL', 'http://localhost:7082'),
        'timeout' => getenv('DADAT_SERVICE_TIMEOUT', 2)
    ]
];
