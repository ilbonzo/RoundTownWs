import 'apache_config.pp'

Exec { path => '/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin' }

#Message of the day file
file { '/etc/motd':
    content => "Welcome to RoundTownWs Vagrant-built virtual machine Managed by Puppet.\n"
}

class apt {
    exec { 'apt-get update':
        timeout => 0
    }
}

class git {
    package { 'git-core':
        ensure => latest,
        require => Class['apt']
    }
}

class php-dev {
    package { ['php5-dev','php-pear', 'php5-curl', 'php5-gd', 'php5-xdebug', 'php5-cli'] :
        ensure => 'latest'
    }

    package { 'curl':
        require => Class['apt']
    }

    package { 'vim':
        require => Class['apt']
    }

    # package { 'make':
    #     require => Class['apt']
    # }

    exec { 'pecl-mongo-install':
        command => 'yes no | pecl install mongo >> /tmp/install.log',
        unless => "pecl info mongo",
        # user => root,
        # group => root,
        require => Package['php-pear', 'php5-dev', 'make'],
    }

    exec { 'install-composer':
        command => 'curl -s https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer',
        creates => '/usr/local/bin/composer',
        require => Package['curl', 'php-pear', 'php5-curl', 'php5-gd', 'php5-xdebug', 'php5-cli']
    }

    exec { 'run-composer':
        command => 'composer --verbose install',
        cwd => "/workspace/www",
        environment => ["COMPOSER_HOME=/home/vagrant/.composer"],
        onlyif  => "test -e /workspace/www/composer.json",
        timeout => 0,
        tries   => 10,
        require => Exec['install-composer']
    }

    exec { 'run vendor installation from deps when composer is not used':
        command => 'php bin/vendors update',
        cwd => "/workspace/www",
        unless  => "test -e /workspace/www/composer.json"
    }

    exec { 'install-phpunit':
        command => '/usr/local/bin/composer global require "phpunit/phpunit=4.2.*"',
        path    => "/usr/local/bin/:/bin/:/usr/bin/",
        environment => ["COMPOSER_HOME=/home/vagrant/.composer"],
        require => Exec['install-composer']
    }

    exec { 'link-phpunit':
        command => 'ln -s /home/vagrant/.composer/vendor/phpunit/phpunit/phpunit /usr/local/bin/phpunit >> /workspace/install.log',
        path    => "/usr/local/bin/:/bin/:/usr/bin/",
        creates => '/usr/local/bin/phpunit',
        require => Exec['install-phpunit']
    }

    file_line { 'Append a line to /etc/php5/apache2/php.ini':
      path => '/etc/php5/apache2/php.ini',
      line => 'extension=mongo.so',
      require => Exec['pecl-mongo-install']
    }

    file_line { 'Append a line to /etc/php5/cli/php.ini':
      path => '/etc/php5/cli/php.ini',
      line => 'extension=mongo.so',
      require => Exec['pecl-mongo-install']
    }
}

class express {
    package { 'express-generator':
        ensure => installed,
        provider => 'npm',
        require => Class['nodejs', 'git']
    }
}

class bower {
    package { 'bower':
        ensure => installed,
        provider => 'npm',
        require => Class['nodejs', 'git']
    }
}

class react_tools {
    package { 'react-tools':
        ensure => installed,
        provider => 'npm',
        require => Class['nodejs', 'git']
    }
}

class {'mongodb':}


include apt
include apache_config
include git
include php-dev
include nodejs
include express
include bower
include react_tools

