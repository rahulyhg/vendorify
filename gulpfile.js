var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir.config.registerWatcher("default", "resources/assets/templates/**/*");

elixir(function(mix) {

    // compile less
    mix.less('app.less');

    // copy fonts
    mix.copy('resources/assets/bower_components/font-awesome/fonts', 'public/fonts');

    // copy templates
    mix.copy('resources/assets/templates', 'public/templates');

    // combine js
    mix.scripts([
            "../bower_components/angular/angular.min.js",
            "../bower_components/angular-route/angular-route.min.js",
            "../bower_components/angular-resource/angular-resource.min.js",
            "../bower_components/angular-animate/angular-animate.min.js",
            "../bower_components/angular-bootstrap/ui-bootstrap.min.js"
        ],
        'public/js/deps.js')

        // plugins
        .scripts([], 
        'public/js/plugins.js')

        // main
        .scripts([
            "app.js",
            "routes.js",
            "resources.js",
            "filters.js",
            "controllers.js",
            "config.js"
        ], 
        'public/js/app.js');

});