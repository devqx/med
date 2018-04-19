/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



angular.module("user-service", [])
        .factory("IPFactory", function($http) {
            return {
                pageSize: 15,
                staff: "",
                domain: location.origin,
                async: function(url, data, successCallback, errorCallback) {
                    $.ajax({
                        url: this.domain+url,
                        type: 'post',
                        data: data,
                        beforeSend: function() {
//                            alert(url)
                            console.log(this.url)
                        },
                        success: successCallback,
                        error: errorCallback
                    });
                },
                async_j: function(url, data, successCallback, errorCallback) {
                    $.ajax({
                        url: this.domain+url,
                        type: 'post',
                        dataType: 'json',
                        data: data,
                        beforeSend: function() {
//                            alert(url)
                            console.log(this.url)
                        },
                        success: successCallback,
                        error: errorCallback
                    });
                }
            }
        })