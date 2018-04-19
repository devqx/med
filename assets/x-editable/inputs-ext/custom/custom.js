/**
Address editable input.
Internally value stored as {city: "Moscow", street: "Lenina", building: "15"}

@class custom
@extends abstractinput
@final
@example
<a href="#" id="address" data-type="custom" data-pk="1">awesome</a>
<script>
$(function(){
    $('#address').editable({
        url: '/post',
        title: 'Enter city, street and building #',
        value: {
            amount: "Moscow",
            expiration: "Lenina"
        }
    });
});
</script>
**/
(function ($) {
    "use strict";
    
    var Custom = function (options) {
        this.init('custom', options, Custom.defaults);
    };

    //inherit from Abstract input
    $.fn.editableutils.inherit(Custom, $.fn.editabletypes.abstractinput);

    $.extend(Custom.prototype, {
        /**
        Renders input from tpl

        @method render() 
        **/        
        render: function() {
           this.$input = this.$tpl.find('input');
        },
        
        /**
        Default method to show value in element. Can be overwritten by display option.
        
        @method value2html(value, element) 
        **/
        value2html: function(value, element) {
            if(!value) {
                $(element).empty();
                return; 
            }
            var html = $('<div>').text(value.amount).html() + ',  Valid till ' + $('<div>').text(value.expiration).html();
            $(element).html(html); 
        },
        
        /**
        Gets value from element's html
        
        @method html2value(html) 
        **/        
        html2value: function(html) {        
          /*
            you may write parsing method to get value by element's html
            e.g. "Moscow, st. Lenina, bld. 15" => {city: "Moscow", street: "Lenina", building: "15"}
            but for complex structures it's not recommended.
            Better set value directly via javascript, e.g. 
            editable({
                value: {
                    amount: "Moscow",
                    expiration: "Lenina"
                }
            });
          */ 
          return null;  
        },
      
       /**
        Converts value to string. 
        It is used in internal comparing (not for sending to server).
        
        @method value2str(value)  
       **/
       value2str: function(value) {
           var str = '';
           if(value) {
               for(var k in value) {
                   str = str + k + ':' + value[k] + ';';  
               }
           }
           return str;
       }, 
       
       /*
        Converts string to value. Used for reading value from 'data-value' attribute.
        
        @method str2value(str)  
       */
       str2value: function(str) {
           /*
           this is mainly for parsing value defined in data-value attribute. 
           If you will always set value by javascript, no need to overwrite it
           */
           return str;
       },                
       
       /**
        Sets value of input.
        
        @method value2input(value) 
        @param {mixed} value
       **/         
       value2input: function(value) {
           if(!value) {
             return;
           }
           this.$input.filter('[name="amount"]').val(value.amount);
           this.$input.filter('[name="expiration"]').val(value.expiration);
           this.$input.filter('[name="reason"]').val(value.reason);
       },
       
       /**
        Returns value of input.
        
        @method input2value() 
       **/          
       input2value: function() { 
           return {
              amount: this.$input.filter('[name="amount"]').val(),
              expiration: this.$input.filter('[name="expiration"]').val(),
              reason: this.$input.filter('[name="reason"]').val()
           };
       },        
       
        /**
        Activates input: sets focus on the first field.
        
        @method activate() 
       **/        
       activate: function() {
            this.$input.filter('[name="amount"]').focus();
       },  
       
       /**
        Attaches handler to submit form in case of 'showbuttons=false' mode
        
        @method autosubmit() 
       **/       
       autosubmit: function() {
           this.$input.keydown(function (e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
           });
       }       
    });

    Custom.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<div class="editable-address "><label class="row-fluid"><span class="span4">Amount: </span><input type="number" name="amount" class="input-small span8"></label></div>'+
             '<div class="editable-address "><label class="row-fluid"><span class="span4">Valid till: </span><input type="date" name="expiration" class="input-small span8"></label></div>' +
        '<div class="editable-address "><label class="row-fluid"><span class="">Reason: </span><input type="text" rows="2" name="reason" class="input-small span12"></label></div>',
             
        inputclass: ''
    });

    $.fn.editabletypes.custom = Custom;

}(window.jQuery));