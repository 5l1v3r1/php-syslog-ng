# $Header: /var/cvsroot/gentoo-x86/app-admin/php-syslog-ng/files/php-syslog-ng.logrotate,v 1.0 2006/09/11 04:05:11 cdukes Exp $
#
# Php-Syslog-ng logrotate snippet for Gentoo Linux
# contributed by Clayton Dukes
#

/var/log/php-syslog-ng/*.log {
  missingok
  compress
  rotate 5
  daily
  postrotate
  /etc/init.d/syslog-ng reload > /dev/null 2>&1 || true
  endscript
}
