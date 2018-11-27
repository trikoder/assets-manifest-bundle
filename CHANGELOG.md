# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).


## [Unreleased]

### Added

## v1.0.1

### Added

* increase range Symfony components

### Changed

* increase range for current Symfony components (>=2.7 <=5.0)

## v1.0

### Added

* support for twig namespaces when linking manifest

### Changed

* [BC break] some of internal public interfaces have changed:
    - ManifestReaderService::getBundleManifest => ManifestReaderService::getManifest
* internal refactorings to support Twig namespaces

## v0.6

### Added

* support publicPath proprety in manifest file:
    * If manifest file contains proprety "publicPath" it's value is always used for generating URLs for assets
    * This isn't used for inline assets, it always includes assets from local filesystem
* Added LICENCE

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
