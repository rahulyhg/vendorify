angular.module('wc')

.directive("asDate", function(){
  return {
   require: 'ngModel',
    link: function(scope, elem, attr, modelCtrl) {
      modelCtrl.$formatters.push(function(modelValue){
        return new Date(modelValue);
      });
    }
  };
})

.filter("asDate", function () {
    return function (input) {
        return new Date(input);
    }
});