

// ---- resources ---- //
var API = '/api/';
angular.module('wc')

// Vendor
.factory('Vendor', ['$resource', function($resource) {
    return $resource(API+'vendor/:vendorId', {vendorId : '@id'}, {
        'update': { method:'PUT' }
    });
}])
.factory('Transaction', ['$resource', function($resource) {
    return $resource(API+'vendor/:vendorId/transaction/:transactionId', {vendorId : '@vendorId', transactionId : '@transactionId'}, {
        'update': { method:'PUT' }
    });
}])
.factory('Report', ['$resource', function($resource) {
    return $resource(API+'report/:reportId', {vendorId : '@id'}, {
        'update': { method:'PUT' }
    });
}]);
