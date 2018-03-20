# Manifest Asset Bundle

This bundle provides Twig helper for generating urls for static public assets.

It reads information from simple JSON config file. There is an example file in
[manifest.json](Tests/Service/test-manifest.json)

## Usage

Bundle provides two Twig functions - manifestAsset and manifestAssetInline.

mainfestAsset takes 2 parameters:

-   asset url in format   @BundleName:path/to/asset or namespaced path @BundleName/path/to/asset
-   optional array with options
    -   for now only option is: "absolute" => if true function returns absolute URIs

Example usage:

```php
<link href="{{ manifestAsset('@AppBundle:css/bundles/home.css') }}" rel="stylesheet" type="text/css">

<script async src="{{ manifestAsset('@AppBundle:js/home.js') }}"></script>
```

manifestAssetInline takes 1 parameter:

-   asset url in format   @BundleName:path/to/asset or namespaced path @BundleName/path/to/asset

It returns the file content of the asset.

Example usage:

```twig
<style>{{ manifestAssetInline('@AppBundle:css/bundles/home.css') }}</style>
```
### Using custom Twig namespaces

First, you need to register your Twig namespace:
```yml
twig:
    paths:
        '%kernel.project_dir%/src/App/Resources': SomeTwigNamepace
```

Then, you can use :
```twig
<style>{{ manifestAsset('@SomeTwigNamepace/css/home.css') }}</style>
<style>{{ manifestAssetInline('@SomeTwigNamepace/js/home.js') }}</style>
```

Note: Twig namespace references does not contain separator `:` like bundles. See [this link](https://symfony.com/doc/current/templating/namespaced_paths.html) for more details.



## Credits

Copyright (C) 2017 Trikoder

Authors: Alen Pokos, Damir Brekalo, Krešo Kunjas.

Contributors: Branimir Đurek

## License

Package is licensed under [MIT License](./LICENSE)
