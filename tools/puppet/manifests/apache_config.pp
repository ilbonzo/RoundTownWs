class { 'apache':
    mpm_module => 'prefork'
}

class {'apache::mod::php': }

class apache_config
{

    apache::vhost { 'api.roundtownws.dev':
        priority        => '1',
        vhost_name      => '*',
        port            => '80',
        docroot         => '/workspace/www/web',
        docroot_owner   => 'vagrant',
        docroot_group   => 'vagrant',
        logroot         => '/var/log',
        override        => 'All',
        setenv          => 'APPLICATION_ENV dev'
    }

    apache::mod { 'rewrite': }

}
