/*

   Form To Wizard originally created by Janko

   Following New features added by iFadey:
   
=> show/hide progress option
=> show/hide step numbers
=> validation callback option
=> rename next, previous button name
=> GOTO step command
=> next step command
=> previous step command

*/
var mainForm;
(function($) {
    $.fn.formToWizard = function( options, cmdParam1 ) {
        if( typeof options !== 'string' ) {
            options = $.extend({
                submitButton      : "",
                showProgress      : true,
                showStepNo        : true,
                validateBeforeNext: null,
                nextBtnName       : 'Next &raquo;',
                prevBtnName       : '&laquo; Back',
                postStepFn        : null
            }, options);
        }
        mainForm = this;
        var element = this
          , steps = $( element ).find( "fieldset" )
          , count = steps.size()
          , submitButtonName = "#" + options.submitButton
          , commands = null;

        if( typeof options !== 'string' ) {
            //hide submit button initially
            //$(submitButtonName).hide();
            setTimeout(function () {
                $(submitButtonName).addClass('next').css({'margin-bottom': '6px'}).detach().appendTo("#step" + (steps.length - 1) + "commands");
            }, 300);

            //assign options to current/selected form (element)
            $( element ).data( 'options', options );
            
            /**************** Validate Options ********************/
            if( typeof( options.validateBeforeNext ) !== "function" )
                options.validateBeforeNext = function() { return true; };
            
            if( options.showProgress ) {
                if( options.showStepNo )
                    $(element).before("<ul id='steps' class='steps'></ul>");
                else
                    $(element).before("<ul id='steps' class='steps breadcrumb'></ul>");
            }
            /************** End Validate Options ******************/
            
            
            steps.each(function(i) {
                $(this).wrap("<div id='step" + i + "' class='stepDetails'></div>");
                $(this).append("<div class='clear' style='margin-bottom: 20px'></div><p class=\"btn-block\" id='step" + i + "commands'></p>");

                if( options.showProgress ) {
                    if( options.showStepNo )
                        $("#steps").append('<li id="stepDesc' + i + '"><a href="javascript:void(0)" onclick="GOTO('+(i+1)+')">Step ' + (i + 1) + "</a><span>" + $(this).find("legend").html() + "</span></li>");
                    else
                        $("#steps").append("<li id='stepDesc" + i + "'>" + $(this).find("legend").html() + "</li>");
                }
                
                if (i == 0) {
                    createNextButton(i);
                    selectStep(i);
                }
                else if (i == count - 1) {
                    $("#step" + i).hide();
                    createPrevButton(i);
                }
                else {
                    $("#step" + i).hide();
                    createPrevButton(i);
                    createNextButton(i);
                }
            });
            
        } else if( typeof options === 'string' ) {
            var cmd = options;
            
            initCommands();
            
            if( typeof commands[ cmd ] === 'function' ) {
                commands[ cmd ]( cmdParam1 );
            } else {
                throw cmd + ' is invalid command!';
            }
        }
        
        
        /******************** Command Methods ********************/
        function initCommands() {
            //restore options object from form element
            options = $( element ).data( 'options' );

            commands = {
                GotoStep: function( stepNo ) {
                    var stepName = "step" + (--stepNo);
                    
                    if( $( '#' + stepName )[ 0 ] === undefined ) {
                        throw 'Step No ' + stepNo + ' not found!';
                    }
                    
                    if( $( '#' + stepName ).css( 'display' ) === 'none' ) {
                        $( element ).find( '.stepDetails' ).hide();
                        $( '#' + stepName ).show();
                        selectStep( stepNo );
                    }
                },
                NextStep: function() {
                    $( '.stepDetails:visible' ).find( 'a.next' ).click();
                },
                PreviousStep: function() {
                    $( '.stepDetails:visible' ).find( 'a.prev' ).click();
                }
            };
        }
        /******************** End Command Methods ********************/
        
        
        /******************** Private Methods ********************/
        function createPrevButton(i) {
            var stepName = "step" + i;
            $("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Prev' class='btn prev'>" + options.prevBtnName + "</a>");

            $("#" + stepName + "Prev").bind("click", function(e) {
                $("#" + stepName).hide();
                $("#step" + (i - 1)).show();
                //$(submitButtonName).hide();
                selectStep(i - 1);
                return false;
            });
        }

        function createNextButton(i) {
            var stepName = "step" + i;
            $("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Next' class='btn next'>" + options.nextBtnName + "</a>");

            $("#" + stepName + "Next").bind( "click", function(e) {
                if( options.validateBeforeNext() === true ) {
                    $("#" + stepName).hide();
                    $("#step" + (i + 1)).show();
                    if (i + 2 == count){
                        //alert("end")
                        //console.log("show the save button");
                        //console.log($(submitButtonName));
                        //$("#" + stepName + "commands").append("<p>Save Button</p>");
                        //$(submitButtonName).show();
                    }
                    selectStep(i + 1);
                }
                
                return false;
            });
        }

        function selectStep(i) {
            if( options.showProgress ) {
                $("#steps li").removeClass("current");
                $("#stepDesc" + i).addClass("current");
            }
            if (typeof options.postStepFn == "function"){
                options.postStepFn();
            }
            //Boxy.get($(".close")).center();
        }
        /******************** End Private Methods ********************/

        
        return $( this );
        
    }
})(jQuery);

var GOTO =function(step){
    mainForm.formToWizard( 'GotoStep', step );
};

var callbackOnStep = function (step) {

};
