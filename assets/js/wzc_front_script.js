function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toGMTString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

jQuery(document).ready(function () {


    wzc_postcode = getCookie("wzc_postcode");

    if (wzc_postcode !== '' && wzc_postcode.length >= 5) {

        jQuery.ajax({
            type: "post",
            dataType: 'json',
            url: wzc_ajax_postajax.ajaxurl,
            data: {
                action: "wzc_check_location",
                postcode: wzc_postcode
            },
            success: function (msg) {
                if (msg.totalrec == 1) {
                    jQuery('.wzcbtn').val("Change");
                    jQuery('.wzcbtn').addClass("wzc_btn");
                    jQuery('.wzc_formbox').addClass("changepincode");
                    jQuery('.wzc_btn').removeClass("wzcbtn");
                    jQuery('.tickbox').show();

                    var date = '';
                    if (msg.showdate == "on") {
                        date = "delivery date : " + msg.deliverydate;
                    }
                    jQuery('.response_pin').html('<div class="divResponse">' + msg.avai_msg + '</div>');
                    jQuery('.wzccheck').prop("disabled", true);


                } else {

                    jQuery('.wzcbtn').val("Change");
                    jQuery('.wzcbtn').addClass("wzc_btn");
                    jQuery('.wzc_formbox').addClass("changepincode");
                    jQuery('.wzc_btn').removeClass("wzcbtn");
                    jQuery('.tickbox').show();

                    jQuery('.wzccheck').prop("disabled", true);
                    jQuery('.response_pin').html('<span class="notavailable">' + wzc_not_srvcbl_txt.not_serving + '</span>');

                }
            }
        });
    }


    jQuery("body").on('click', '.wzcbtn', function () {

        var postcode = jQuery('.wzccheck').val();

        if (postcode !== '' && postcode.length == 5) {
            jQuery('.response_pin').html('<p class="wzcloading">Loading..</p>');
            jQuery.ajax({
                type: "post",
                dataType: 'json',
                url: wzc_ajax_postajax.ajaxurl,
                data: {
                    action: "wzc_check_location",
                    postcode: postcode
                },
                success: function (msg) {
                    if (msg.totalrec == 1) {
                        jQuery('.wzcbtn').val("Change");
                        jQuery('.wzcbtn').addClass("wzc_btn");
                        jQuery('.wzc_formbox').addClass("changepincode");
                        jQuery('.wzc_btn').removeClass("wzcbtn");
                        jQuery('.tickbox').show();
                        var date = '';
                        if (msg.showdate == "on") {
                            date = "delivery date : " + msg.deliverydate;
                        }
                        jQuery('.response_pin').html('<div class="divResponse">' + msg.avai_msg + '</div>');
                        jQuery('.wzccheck').prop("disabled", true);


                    } else {
                        jQuery('.wzcbtn').val("Change");
                        jQuery('.wzcbtn').addClass("wzc_btn");
                        jQuery('.wzc_formbox').addClass("changepincode");
                        jQuery('.wzc_btn').removeClass("wzcbtn");
                        jQuery('.tickbox').show();

                        jQuery('.wzccheck').prop("disabled", true);
                        jQuery('.response_pin').html('<span class="notavailable">' + wzc_not_srvcbl_txt.not_serving + '</span>');

                    }
                }
            });
        } else {
            alert('Please Enter a valid pincode');
        }

    });

    jQuery("body").on('click', '.wzc_btn', function () {
        setCookie('wzc_postcode', '', 0);
        jQuery('.wzccheck').val('').prop("disabled", false);
        jQuery('.response_pin').html('<p class="pincode-enterPincode">Please enter PIN code to check delivery time &amp; Pay on Delivery Availability</p>');
        jQuery('.wzc_btn').val("Check");
        jQuery('.wzc_btn').addClass("wzcbtn");
        jQuery('.wzcbtn').removeClass("wzc_btn");
        jQuery('.wzc_formbox').removeClass("changepincode");
        jQuery('.wzcbtn').show();

        jQuery('.tickbox').hide();
    });



});


