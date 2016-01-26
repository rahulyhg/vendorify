/**
 * Vendorify Angular UI
 *  - Angular front end for the vendorify admin application
 *
 * @author Michael Roth <mr.hroth@gmail.com>
 * @version 2.0.1
 */

// ---- app main ---- //
angular.module('wc', [
    'ngRoute', 'ngResource','ngAnimate','ui.bootstrap'
], function($interpolateProvider) {
    
    // play nicely with laravel blade
    $interpolateProvider.startSymbol('<%');
    $interpolateProvider.endSymbol('%>');
    
});
