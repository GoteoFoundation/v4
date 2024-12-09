<?php

namespace Deployer;

require 'recipe/symfony.php';

// Config

set('repository', 'git@github.com:GoteoFoundation/v4.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('v4.goteo.org')
  ->set('remote_user', 'deployer')
  ->set('deploy_path', '~/v4')
  ->set('dotenv_example', '.env.prod')
  ->set('console_options', '--env=prod')
  ->set('composer_options', '--verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --no-scripts');


// Hooks

after('deploy:failed', 'deploy:unlock');
