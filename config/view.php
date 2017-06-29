<?php

return [
    /**
     * View storage paths.
     */
    'paths'    => [ realpath(base_path('resources/views')) ],

    /**
     * Compiled view path.
     */
    'compiled' => realpath(storage_path('framework/views')),
];
