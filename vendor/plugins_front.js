// Avoid `console` errors in browsers that lack a console.

(function() {

    var method;

    var noop = function() {

    };

    var methods = [

        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',

        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',

        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',

        'timeStamp', 'trace', 'warn'

    ];

    var length = methods.length;

    var console = (window.console = window.console || {});



    while (length--) {

        method = methods[length];



        // Only stub undefined methods.

        if (!console[method]) {

            console[method] = noop;

        }

    }

}());



// Place any jQuery/helper plugins in here.

//$.fn.LoadMenu = function(xmlFile) {

//    var menu = this[0];

//    $(menu).html("");

//    $.ajax({

//        type: "GET",

//        url: xmlFile, //"menu.xml",

//        dataType: "xml",

//        success: function(xml) {

//            var ul_main = $(menu);//$("#menu_wrapper");

//            $(xml).find("Menu").each(function() {

//                if ($(this).children().length)

//                {

//                    var ulSub = $("<ul class='dropdown-menu' role='menu' />");

//                    $(this).children().each(function() {

//                        ulSub.append("<li id=" + $(this).attr("id") + "><a href=" + $(this).attr("url") + ">" + $(this).attr("text") + "</a></li>");

//                    });

//                    var li = $("<li class='dropdown' id=" + $(this).attr("id") + ">" +

//                            "<a class='dropdown-toggle' data-toggle='dropdown' data-href=" + $(this).attr("url") +

//                            " href=# >" + $(this).attr("text") + "</a></li>");

//                    ul_main.append(li.append(ulSub));

//                }

//                else

//                {

//                    ul_main.append("<li id=" + $(this).attr("id") + "><a href=" + $(this).attr("url") + ">" + $(this).attr("text") + "</a></li>");

//                }

//            });

//        },

//        error: function(x, a, c) {

//            var ul_main = $("#menu_wrapper");

//        }

//    });

//};

//
