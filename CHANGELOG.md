# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Releases

### [1.0.2] - 2024-12-07

* Add ixnode/php-coordinate package with version 1.0.0

### [1.0.1] - 2024-12-07

* Add ixnode/php-coordinate package with version 0.1.17

### [1.0.0] - 2024-12-07

* Implement Imagick engine in addition to GdImage

### [0.1.3] - 2024-05-24

* Add width option to cli command

### [0.1.2] - 2023-11-20

* Add image string to CliImage

### [0.1.1] - 2023-08-14

* Refactoring
* Add new Point class

### [0.1.0] - 2023-08-14

* Initial release with first CliImage builder and converter
* Add src
* Add tests
  * PHP Coding Standards Fixer
  * PHPMND - PHP Magic Number Detector
  * PHPStan - PHP Static Analysis Tool
  * PHPUnit - The PHP Testing Framework
  * Rector - Instant Upgrades and Automated Refactoring
* Add README.md
* Add LICENSE.md

## Add new version

```bash
# Checkout master branch
$ git checkout main && git pull

# Check current version
$ vendor/bin/version-manager --current

# Increase patch version
$ vendor/bin/version-manager --patch

# Change changelog
$ vi CHANGELOG.md

# Push new version
$ git add CHANGELOG.md VERSION && git commit -m "Add version $(cat VERSION)" && git push

# Tag and push new version
$ git tag -a "$(cat VERSION)" -m "Version $(cat VERSION)" && git push origin "$(cat VERSION)"
```
