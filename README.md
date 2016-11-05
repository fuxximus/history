# history
Source Control history, deployment tracker

This is a very generic, web based at the moment only SVN project tracker.

The initial purpose of this was to track deployment JAVA servlets, because of unstable platform war files needed to be undeployed and redeployed constantly. Tracking those projects became a problem. Thus this little project was born. Does not use any 3rd party libraries that are not default with PHP. No security measures were taken, project should not reside publicly.
requires/uses (as far as I know):
  * php
    * cURL
    * simpleXML
  * mysql
  * jquery

It backs up supplied files for future rollbacks. Uses cURL to pull SVN log histories from Apache DavSvn. 

TODO:
  * non DavSVN implementation
  * git implementation
  * encrypt passwords
  * pagers
  * logging?

![Screenshot](/ss.jpg?raw=true "Screen Shot")
