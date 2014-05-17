
var LifeApp = angular.module('LifeApp', []);

LifeApp
        .filter('nToBR', function() {
            return function(text) {
                return text.replace(/\n/g, '<br/>');
            };
        })
        .directive('eatClick', function() {
            return function(scope, element, attrs) {
                $(element).click(function(event) {
                    event.preventDefault();
                });
            }
        })
        .directive('eatPropagation', function() {
            return function(scope, element, attrs) {
                $(element).click(function(event) {
                    event.cancelBubble = true;
                    event.stopPropagation && event.stopPropagation();
                });
            }
        })