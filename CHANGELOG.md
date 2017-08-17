# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).


## [Unreleased]

### Added

* mainfest file generator


## v0.5

### Changed

* inlined assets are now marked safe in html context, so when using in templates
  raw twig filter isn't needed any more


## v0.4

### Added

* added asset inlining, new twig function manifestAssetInline
* new config option web_dir (default value "web"), to define name
  of the public web folder
* minor refactorings

## v0.3

### Changed

* simplify folder structure
* update psr-4 autoloader with new structure


## v0.2

### Changed

* update psr-4 autoloader


## v0.1

* inital release
