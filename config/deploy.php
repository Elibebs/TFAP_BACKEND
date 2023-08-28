<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default deployment strategy
    |--------------------------------------------------------------------------
    |
    | This option defines which deployment strategy to use by default on all
    | of your hosts. Laravel Deployer provides some strategies out-of-box
    | for you to choose from explained in detail in the documentation.
    |
    | Supported: 'basic', 'firstdeploy', 'local', 'pull'.
    |
    */

    'default' => 'basic',
    'default_stage' => 'dev',

    /*
    |--------------------------------------------------------------------------
    | Custom deployment strategies
    |--------------------------------------------------------------------------
    |
    | Here, you can easily set up new custom strategies as a list of tasks.
    | Any key of this array are supported in the `default` option above.
    | Any key matching Laravel Deployer's strategies overrides them.
    |
    */

    'strategies' => [
        'git' => [
            'hook:start',
            'git:push',         // Tasks hooked to `start` will be called here.
            'deploy:prepare',
            'deploy:lock',
            'deploy:release',
            'deploy:update_code',
            'deploy:shared',
            'deploy:vendors',
            'hook:build',           // Tasks hooked to `build` will be called here.
            'deploy:writable',
            'hook:ready',           // Tasks hooked to `ready` will be called here.
            'deploy:symlink',
            'deploy:unlock',
            'cleanup',
            'hook:done',            // Tasks hooked to `done` will be called here.
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Hooks
    |--------------------------------------------------------------------------
    |
    | Hooks let you customize your deployments conveniently by pushing tasks 
    | into strategic places of your deployment flow. Each of the official
    | strategies invoke hooks in different ways to implement their logic.
    |
    */


    'hooks' => [
        // Right before we start deploying.
        'start' => [
            'slack:notify',
        ],

        // Code and composer vendors are ready but nothing is built.
        'build' => [
             //'npm:install',
            // 'npm:production',
        ],

        // Deployment is done but not live yet (before symlink)
        'ready' => [
            'artisan:storage:link',
            'artisan:view:clear',
            'artisan:cache:clear',
            //'artisan:config:cache',
            'artisan:migrate',
            // 'artisan:horizon:terminate',
        ],

        // Deployment is done and live
        'done' => [
            'fpm:reload',
            // 'artisan:tenancy:migrate',
        ],

        // Deployment succeeded.
        'success' => [
            'slack:notify:success',
            //'artisan:backup:clean',
           // 'artisan:backup:run',
            // 'artisan:system:backup:clean',
            // 'artisan:system:backup:run',
        ],

        // Deployment failed.
        'fail' => [
            'slack:notify:failure',
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Deployment options
    |--------------------------------------------------------------------------
    |
    | Options follow a simple key/value structure and are used within tasks
    | to make them more configurable and reusable. You can use options to
    | configure existing tasks or to use within your own custom tasks.
    |
    */

    'options' => [
        'application' => env('APP_NAME', 'TemaFirstAutoParts'),
        'repository' => 'git@github.com:ijiKod/TFAP_BACKEND.git',
        'php_fpm_service' => 'php7.3-fpm',
        'slack_webhook' => 'https://hooks.slack.com/services/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',
    ],

    /*
    |--------------------------------------------------------------------------
    | Hosts
    |--------------------------------------------------------------------------
    |
    | Here, you can define any domain or subdomain you want to deploy to.
    | You can provide them with roles and stages to filter them during
    | deployment. Read more about how to configure them in the docs.
    |
    */

    'hosts' => [
        'dev' => [
            'hostname' => '18.133.243.135',
            'deploy_path' => '/home/ubuntu/dev/tfap',
            'user' => 'ubuntu',
            'identityFile' => '~/.ssh/LightsailDefaultKey-eu-west-TFAP.pem',
        ],

        'qa' => [
            'hostname' => '18.133.243.135',
            'deploy_path' => '/home/ubuntu/qa/tfap',
            'user' => 'ubuntu',
            'identityFile' => '~/.ssh/LightsailDefaultKey-eu-west-TFAP.pem',
        ],

        'live' => [
            'hostname' => '18.133.243.135',
            'deploy_path' => '/home/ubuntu/live/tfap',
            'user' => 'ubuntu',
            'identityFile' => '~/.ssh/LightsailDefaultKey-eu-west-TFAP.pem',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Localhost
    |--------------------------------------------------------------------------
    |
    | This localhost option give you the ability to deploy directly on your
    | local machine, without needing any SSH connection. You can use the
    | same configurations used by hosts to configure your localhost.
    |
    */

    'localhost' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Include additional Deployer recipes
    |--------------------------------------------------------------------------
    |
    | Here, you can add any third party recipes to provide additional tasks, 
    | options and strategies. Therefore, it also allows you to create and
    | include your own recipes to define more complex deployment flows.
    |
    */

    'include' => [
        'recipe/slack.php',
       // 'recipe/backup.php',
         //'recipe/push.php'
        ],

    /*
    |--------------------------------------------------------------------------
    | Use a custom Deployer file
    |--------------------------------------------------------------------------
    |
    | If you know what you are doing and want to take complete control over
    | Deployer's file, you can provide its path here. Note that, without
    | this configuration file, the root's deployer file will be used.
    |
    */

    'custom_deployer_file' => false,

];