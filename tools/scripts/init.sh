#!/bin/bash
mkdir -p /etc/puppet/modules
touch /etc/puppet/hiera.yaml

#install puppet modules
puppet module install puppetlabs-apache
puppet module install puppetlabs-mongodb

apt-get update
