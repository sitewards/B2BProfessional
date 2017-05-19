Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to
[Semantic Versioning](http://semver.org/).

## 4.0.1

### Changed

- Removed orphaned files from the previous deletion of Netzarbeiter/Customer-Activation, hard coded in the repo. @jhoelzl

### Notes

Special thanks to @jhoelzl, who has driven the current batch of improvements to this extension. It's surely appreciated!

## 4.0.0

### Changed

- [BREAKING] This removes an entire module from this repository, instead preferring to install it via composer. While
  this should not have any functional changes, if you do not use composer to install or manage your modules this will
  no longer work. Thanks @andrewhowdencom
- Upgraded Netzarbeiter/Customer-Activation to 0.5.7. Thanks @jhoelzl

## 3.0.7

### Added
- Changelog

### Fixed

- Removed some spam in the system.log file. Thanks @jhoelzl
