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
    package { ['php-pear', 'php5-curl', 'php5-gd', 'php5-xdebug'] :
        ensure => 'latest'
    }

    package { 'curl':
        require => Class['apt']
    }

    package { 'vim':
        require => Class['apt']
    }

    package { 'make':
        require => Class['apt']
    }

    exec { 'pecl-mongo-install':
        command => 'pecl install mongo',
        unless => "pecl info mongo",
        require => Package['php-pear', 'make'],
    }

    exec { 'install-composer':
        command => 'curl -s https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer',
        creates => '/usr/local/bin/composer',
        require => Package['curl', 'php-pear', 'php5-curl', 'php5-gd', 'php5-xdebug']
    }

    exec { 'run composer for silex when composer is used':
        command => 'composer --verbose install',
        cwd => "/workspace/www",
        onlyif  => "test -e /workspace/www/composer.json",
        timeout => 0,
        tries   => 10,
        require => Exec['install-composer'],
      }

    exec { 'run vendor installation from deps when composer is not used':
        command => 'php bin/vendors update',
        cwd => "/workspace/www",
        unless  => "test -e /workspace/www/composer.json",
      }
}

class {'mongodb':}

include apt
include apache_config
include git
include php-dev
