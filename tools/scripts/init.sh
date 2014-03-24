#!/bin/bash
mkdir -p /etc/puppet/modules

#install puppet modules
puppet module install puppetlabs-apache
puppet module install puppetlabs-mongodb

apt-get update
