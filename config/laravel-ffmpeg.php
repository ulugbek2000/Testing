<?php

return [
    'ffmpeg' => [
        'binaries' => env('FFMPEG_BINARIES', 'home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffmpeg'),
        'threads'  => 12,
    ],
    'ffprobe' => [
        'binaries' => env('FFPROBE_BINARIES', 'home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffprobe')
    ],

    'timeout' => 3600,

    'log_channel' => env('LOG_CHANNEL', 'stack'),   // set to false to completely disable logging

    'temporary_files_root' => env('FFMPEG_TEMPORARY_FILES_ROOT', sys_get_temp_dir()),

    'temporary_files_encrypted_hls' => env('FFMPEG_TEMPORARY_ENCRYPTED_HLS', env('FFMPEG_TEMPORARY_FILES_ROOT', sys_get_temp_dir())),
];
