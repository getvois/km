var $api_url = 'http://api.travelwebpartner.com/api/item.filter/';
var $dpInterval = '1m';
var $dpReturnInterval = '1m';
var $offerPerPageLimit = 3;
$(document).ready(function() {
    var $body = $("body");
    var $lang = $body.data('lang');

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {

        if($(e.target).data('type') == 'packages'){
            var container = document.querySelector('#package-holder');
            new Isotope( container, {itemSelector: '.package'});
            //$('#package-holder').css('height', '100px');
        }
        if($(e.target).data('type') == 'club'){
            var container = document.querySelector('#offer-holder');
            new Isotope( container, {itemSelector: '.offer'});
            //$('#offer-holder').css('height', '100px');
        }

        if($(e.target).data('form')){
            if(!$($(e.target).data('form')).is(':visible')){
                $('.form-header:visible').removeClass('hide').slideUp();
                $($(e.target).data('form')).slideDown();
            }
        }
    });
    //hide because of isotope behavior
    $('#package-pager').hide();
    $('#offer-pager').hide();


    //select first tab to show the form
    $('.form-header').removeClass('hide').hide();
    $('.form-header:first').show();

    //show tab based on page
    if($body.data('tab')){
        $('a[data-type="'+$body.data('tab')+'"]').tab('show');
    }

    //club page
    if(window.location.pathname.match(/\/club/)){
        $('#club-tab').tab('show');
    }

    $(".datepick").datepick($.extend({
            dateFormat: 'dd.mm.yyyy',
            //rangeSelect: true,
            //monthsToShow: 2,
            //minDate: '+1d',
            changeMonth: false,
            renderer: $.extend({}, $.datepick.defaultRenderer,
                {picker: $.datepick.defaultRenderer.picker.
                    replace(/\{link:buttons}/, '')
                    .replace(/\{link:today}/, '')
                })
        },
        $.datepick.regionalOptions[$lang]));

    $('#package-country').change(function () {
        var $cityId = $(this).val();
        $('#package-place').attr('disabled', true).val('-1');
        if($cityId != -1){
            $.get('/package-citylist/' + $cityId, function (responce) {
                $('#package-place').html(responce).attr('disabled', false);
            });
        }else{
            $('#package-hotel').attr('disabled', true).val('-1');
        }
    });

    $('#offer-country').change(function () {
        var $cityId = $(this).val();
        $('#offer-place').attr('disabled', true).val('-1');
        if($cityId != -1){
            $.get('/offer-citylist/' + $cityId, function (responce) {
                $('#offer-place').html(responce).attr('disabled', false);
            });
        }else{
            $('#offer-hotel').attr('disabled', true).val('-1');
        }
    });


    $('#package-place').change(function () {
        var $placeId = $(this).val();
        $('#package-hotel').attr('disabled', true).val('-1');
        if($placeId != -1){
            $.get('/package-hotellist/' + $placeId, function (responce) {
                $('#package-hotel').html(responce).attr('disabled', false);
            });
        }
    });

    $('#hotel-filter').click(function () {
        $(this).addClass('disabled');

        var $place = $('#hotel-place').val();

        $.get('/hotel-filter/?place='+$place, function (responce) {
            var $package = $('#hotel-holder');
            $package.html(responce.html);

            $('#hotel-filter').removeClass('disabled');
        });

        return false;
    });


    $('#package-filter').click(function () {
        checkHolderHeight('#package-holder');
        $(this).addClass('disabled');

        var $from = $('#package-date-from').datepick('getDate');
        var $place = $('#package-place').val();
        var $hotel = $('#package-hotel').val();

        if($from.length > 0) $from = $from[0].toMysqlFormat();
        else $from = '';

        $.get('/package-filter/?hotel='+$hotel+'&place='+$place + "&from=" + $from, function (responce) {
            var $package = $('#package-holder');
            $package.html(responce.html);

            if($package.find('> div').length < responce.total){
                $('#package-pager').show();
            }else{
                $('#package-pager').hide();
            }

            $('#package-filter').removeClass('disabled');
            $('a[data-type="packages"]').tab('show');
        });

        return false;
    });

    function checkHolderHeight($holderId){
        if($($holderId).outerHeight() < 200 ){
            $($holderId).css('height', 'auto');
            return true;
        }
        return false;
    }

    $('#package-pager').find('a').click(function () {

        var $package3 = $('#package-pager');
        if(checkHolderHeight('#package-holder')) {
            $package3.hide();
            return false;
        }

        $package3.find('a').addClass('disabled');
        var $from = $('#package-date-from').datepick('getDate');
        var $place = $('#package-place').val();
        var $hotel = $('#package-hotel').val();

        var $offset = $('#package-holder').find('> div').length;

        $.get('/package-filter/?offset='+$offset+'&hotel='+$hotel+'&place='+$place + "&from=" + $from, function (responce) {
            var $package = $('#package-holder');
            $package.append(responce.html);
            var $package2 = $('#package-pager');
            $package2.find('a').removeClass('disabled');

            if($package.find('> div').length < responce.total){
                $package2.show();
            }else{
                $package2.hide();
            }
        });

        return false;

    });

    $('#offer-pager').find('a').click(function () {

        var $pager = $('#offer-pager');
        if(checkHolderHeight('#offer-holder')) {
            $pager.hide();
            return false;
        }

        $pager.find('a').addClass('disabled');
        var $place = $('#offer-place').val();
        var $country = $('#offer-country').val();

        var $offset = $('#offer-holder').find('> div').length;

        $.get('/offer-filter/?offset='+$offset+'&country='+$country+'&place='+$place, function (responce) {
            var $package = $('#offer-holder');
            $package.append(responce.html);
            var $package2 = $('#offer-pager');
            $package2.find('a').removeClass('disabled');

            if($package.find('> div').length < responce.total){
                $package2.show();
            }else{
                $package2.hide();
            }
        });

        return false;

    });

    $('#offer-filter').click(function () {
        checkHolderHeight('#offer-holder');
        $(this).addClass('disabled');

        var $place = $('#offer-place').val();
        var $country = $('#offer-country').val();

        $.get('/offer-filter/?country='+$country+'&place='+$place, function (responce) {
            var $package = $('#offer-holder');
            $package.html(responce.html);
            $('#offer-filter').removeClass('disabled');
            $('a[data-type="club"]').tab('show');
            var $package2 = $('#offer-pager');
            if($package.find('> div').length < responce.total){
                $package2.show();
            }else{
                $package2.hide();
            }

        });

        return false;
    });

    //noinspection JSUnresolvedVariable,JSUnusedLocalSymbols
    var $packageCalendar = $('.package-calendar');
    var calendar = $packageCalendar.calendar(
        {
            tmpl_path: "/bundles/sandboxwebsite/frontend/js/calendar/tmpls/",
            events_source: '/package-event-source/' + $packageCalendar.data('package-id')
        });

    calendar.setLanguage($lang);
    //calendar.view();

    var control = $(".package-calendar-control");
    control.find('.package-calendar-navigate').click(function () {
        calendar.navigate($(this).data('calendar-nav'));
    });


    //$('#club-filter').click(function () {
    //    var $container = $("#offers-container");
    //    $container.html("");
    //
    //    loadMoreOffers();
    //    return false;
    //});


    //var $container = $("#offers-container");
    //$container.html("");
    //loadMoreOffers();

    $("#show-club-tab").click(function () {
        $('#club-tab').tab('show');
        return false;
    });

    $("#open-user-tab").click(function () {
        $('#user-tab').tab('show');
        return false;
    });

    $("#reset-user-password").click(function () {
        $(this).addClass('disabled');
        $.get('/reset-password/', function (responce) {
            var $flashbag = $("#info-modal");
            if(responce.status == 'error'){
                $flashbag.find('.modal-body').html("<div>" + responce.msg + "</div>");
            }else{
                $flashbag.find('.modal-body').html("<div>" + responce.msg + "</div>");
            }

            $flashbag.modal('show');
            $("#reset-user-password").removeClass('disabled');
        });
        return false;
    });


    $("#reset-password").click(function () {
        $(this).addClass('disabled');
        $.get('/password-reset/' + $("#reset-email").val(), function (responce) {
            var $flashbag = $("#info-modal");
            if(responce.status == 'error'){
                $flashbag.find('.modal-body').html("<div>" + responce.msg + "</div>");
            }else{
                $flashbag.find('.modal-body').html("<div>" + responce.msg + "</div>");
            }

            $flashbag.modal('show');
            $("#reset-password").removeClass('disabled');
        });
        return false;
    });

    $("#save-user-details").click(function () {
        var $data = $("#save-user-details").closest('form').serialize();

        $(this).addClass('disabled');
        $.get('/user-edit/?'+$data, function (responce) {
            var $flashbag = $("#info-modal");
            if(responce.status == 'error'){
                $flashbag.find('.modal-body').html("<div>" + responce.msg + "</div>");
            }else{
                $flashbag.find('.modal-body').html("<div>" + responce.msg + "</div>");
            }

            $flashbag.modal('show');
            $("#save-user-details").removeClass('disabled');
        });
        return false;
    });

    $("#save-user-password").click(function () {
        var $data = $("#save-user-password").closest('form').serialize();

        $(this).addClass('disabled');
        $.get('/user-password/?'+$data, function (responce) {
            var $flashbag = $("#info-modal");
            if(responce.status == 'error'){
                $flashbag.find('.modal-body').html("<div>" + responce.msg + "</div>");
            }else{
                $flashbag.find('.modal-body').html("<div>" + responce.msg + "</div>");
            }

            $flashbag.modal('show');
            $("#save-user-password").removeClass('disabled');
        });
        return false;
    });

    $("#offercountry").change(function () {
        var $code = $(this).val();
        if($code.length > 0){
            $.get('http://api.travelwebpartner.com/app_dev.php/api/offer.getCitiesByCountry/' + $code, function (responce) {
                if(responce.error){
                    $("#select-city").html("");
                    alert(responce.message)
                }else{
                    var $select = '<select name="offercity" id="offercity">';
                    $select += "<option value=''>Any</option>";
                    for(var i=0;i<responce.length;i++){
                        $select += "<option value='"+responce[i].id+"'>" + responce[i]['cityName_' + $lang] + "</option>";
                    }
                    $select += "</select>";

                    $("#select-city").html($select);
                }
            });
        }else{
            $("#select-city").html("");
        }
    });

    $("#register-submit").closest('form').submit(function () {

        $("#register-submit").addClass('disabled');
        $.get('/register-ajax/?' + $(this).serialize(), function (responce) {
            var $flashbag = $("#info-modal");
            if(responce.status == 'error'){
                $flashbag.find('.modal-body').html("<div>" + responce.msg + "</div>");
            }else{
                $flashbag.find('.modal-body').html("<div>" + responce.msg + "</div>");
                setTimeout(function () {
                    window.location = window.location;
                }, 1000);
            }

            $flashbag.modal('show');
            $("#register-submit").removeClass('disabled');
        });
        return false;
    });

    var $tree = $('.subscribe-tree');
    $tree.jstree({
        "checkbox" : {
            "keep_selected_style" : false
        },
        "plugins" : [ "checkbox" ]
    });
    $tree.jstree('hide_icons');
    setTimeout(function () {
        $tree.jstree('close_all');

    }, 500);

    $('.tree-toggle').click(function () {
        if($tree.jstree('is_open', 'node_0')){
            $tree.jstree('close_node', 'node_0');
            $(this).removeClass('fa-chevron-up');
            $(this).addClass('fa-chevron-down');
        }else{
            $tree.jstree('open_node', 'node_0');
            $(this).addClass('fa-chevron-up');
            $(this).removeClass('fa-chevron-down');
        }


    });

    $('#form_submit').closest('form').submit(function () {
        var $selected = $('.subscribe-tree').jstree('get_selected');

        var $ids = '';
        for(var i=0; i<$selected.length; i++){
            $ids += $selected[i].replace('node_', '');
            if(i != $selected.length - 1 ){
                $ids += ",";
            }
        }

        if($ids.length == 0) return false;

        $('#form_node').val($ids);

        $('#form_submit').addClass('disabled');
        $.post('/subscribe/', $(this).serialize(), function (responce) {
            var $flashbag = $("#info-modal");
            if(responce.status == 'error'){
                $flashbag.find('.modal-body').html("<div>" + responce.msg + "</div>");
            }else{
                $flashbag.find('.modal-body').html("<div>" + responce.msg + "</div>");
            }

            $flashbag.modal('show');
            $('#form_submit').removeClass('disabled');
        });

        return false;
    });

    //noinspection JSUnresolvedFunction,JSUnresolvedVariable
    $(".datepick-input").datepick($.extend({
            dateFormat: 'dd.mm.yyyy',
            rangeSelect: true,
            monthsToShow: 2,
            minDate: '+1d',
            changeMonth: false
        },
        $.datepick.regionalOptions[$lang]));

    var $datepickFrom = $('#datepick-input-from');
    var $datepickTo = $('#datepick-input-to');

    $('#datepick-input-to-holder').attr('placeholder', window.trans.returnDate[$lang]).val('');

    var $datepickerClearIcon = $(".datepicker-clear-icon");
    $datepickerClearIcon.hide();
    $datepickerClearIcon.removeClass('hide');

    $datepickerClearIcon.click(function () {
        //noinspection JSUnresolvedFunction
        $datepickTo.datepick('clear');
        $('#datepick-input-to-holder').attr('placeholder', window.trans.returnDate[$lang]).val('');
        $datepickerClearIcon.hide();
    });

    $('#datepick-input-from-holder, #datepick-trigger-from').click(function () {
        //noinspection JSUnresolvedFunction
        $datepickFrom.datepick('show');
    });
    $('#datepick-input-to-holder, #datepick-trigger-to').click(function () {
        //noinspection JSUnresolvedFunction
        $datepickTo.datepick('show');
    });

    //noinspection JSUnresolvedFunction
    $datepickFrom.datepick('option', 'onSelect', function (dates) {
        if(dates.length == 0) return;
        //noinspection JSUnresolvedFunction
        var $dates = $(this).datepick('getDate');
        var $date = new Date($dates[0]);

        var pattern = /([0-9]+)\s*(d|w|m|y)?/g;
        var matches = pattern.exec($dpInterval);
        //noinspection JSUnresolvedVariable
        $.datepick.add($date, parseInt(matches[1], 10), matches[2] || 'd');

        $dates[0] = new Date($dates[0]);
        $dates[1] = $date;
        //noinspection JSUnresolvedFunction
        $(this).datepick('setDate', $dates);

        //noinspection JSUnresolvedFunction
        $datepickTo.datepick('option', 'minDate', $dates[0]);

        if($dpInterval == '0d'){
            //noinspection JSUnresolvedVariable
            $('#datepick-input-from-holder').val($.datepick.formatDate('dd.mm.yyyy', $dates[0]));
        }else{
            //noinspection JSUnresolvedVariable
            $('#datepick-input-from-holder').val($.datepick.formatDate('dd.mm.yyyy', $dates[0]) + " – ");
        }
    });

    //noinspection JSUnresolvedFunction
    $datepickTo.datepick('option', 'onSelect', function (dates) {
        if(dates.length == 0) return;
        //noinspection JSUnresolvedFunction
        var $dates = $(this).datepick('getDate');
        var $date = new Date($dates[0]);

        var pattern = /([0-9]+)\s*(d|w|m|y)?/g;
        var matches = pattern.exec($dpReturnInterval);
        //noinspection JSUnresolvedVariable
        $.datepick.add($date, parseInt(matches[1], 10), matches[2] || 'd');

        $dates[0] = new Date($dates[0]);
        $dates[1] = $date;
        //noinspection JSUnresolvedFunction
        $(this).datepick('setDate', $dates);

        if($dpReturnInterval == '0d'){
            //noinspection JSUnresolvedVariable
            $('#datepick-input-to-holder').val($.datepick.formatDate('dd.mm.yyyy', $dates[0]));
        }else{
            //noinspection JSUnresolvedVariable
            $('#datepick-input-to-holder').val($.datepick.formatDate('dd.mm.yyyy', $dates[0]) + " – ");
        }

        $datepickerClearIcon.show();
    });

    //noinspection JSUnresolvedFunction,JSUnusedLocalSymbols
    $datepickFrom.datepick('option', 'onShow', function (dates) {
        var $interval = $('.dp-interval');
        $interval.removeClass('active');
        $interval.each(function () {
            if($(this).data('interval') == $dpInterval){
                $(this).addClass('active');
            }
        });
    });

    //noinspection JSUnresolvedFunction,JSUnusedLocalSymbols
    $datepickTo.datepick('option', 'onShow', function (dates) {
        var $interval = $('.dp-interval');
        $interval.removeClass('active');
        $interval.each(function () {
            if($(this).data('interval') == $dpReturnInterval){
                $(this).addClass('active');
            }
        });
    });

    //noinspection JSUnresolvedFunction,JSUnresolvedVariable
    $datepickFrom.datepick('option', 'renderer', $.extend({}, $.datepick.defaultRenderer,
        {picker: $.datepick.defaultRenderer.picker.
            replace(/\{link:buttons}/, buttonPanel("#datepick-input-from"))
            .replace(/\{link:today}/, '')
        }));

    //noinspection JSUnresolvedFunction,JSUnresolvedVariable
    $datepickTo.datepick('option', 'renderer', $.extend({}, $.datepick.defaultRenderer,
        {picker: $.datepick.defaultRenderer.picker.
            replace(/\{link:buttons}/, buttonPanel("#datepick-input-to"))
            .replace(/\{link:today}/, '')
        }));


    //noinspection JSUnresolvedFunction
    $datepickFrom.datepick('setDate', ['+1d', '+1m']);


    //setField(); //pre fill destination field
    var $form = $("#travelbase-form");
    $form.submit(function () {
        return false;
    });
    var $city_picker = $(".city-picker");


    $body.on('click', '.dp-interval', function () {
        var $target = $($(this).data('target'));

        if($(this).data('target') == '#datepick-input-from'){
            $dpInterval = $(this).data('interval');
        }else{
            $dpReturnInterval = $(this).data('interval');
        }

        //noinspection JSUnresolvedFunction
        var $dates = $target.datepick('getDate');
        if($dates.length == 0) return;
        var $date = new Date($dates[0]);

        var pattern = /([0-9]+)\s*(d|w|m|y)?/g;
        var matches = pattern.exec($(this).data('interval'));
        //noinspection JSUnresolvedVariable
        $.datepick.add($date, parseInt(matches[1], 10), matches[2] || 'd');

        $dates[0] = new Date($dates[0]);
        $dates[1] = $date;

        //noinspection JSUnresolvedFunction
        $target.datepick('setDate', $dates);
    });

    //$body.on('click', '.dp-close', function () {
    //    var $target = $($(this).data('target'));
    //    $target.datepick('hide');
    //});

    function buttonPanel($id){
        var interval = 0;
        if($id == '#datepick-input-from'){
            interval = $dpInterval;
        }else{
            interval = $dpReturnInterval;
        }
//"<button data-target='" + $id + "' class='btn btn-default dp-close' >close</button>" +
        return "<button data-target='" + $id + "' class='btn btn-default dp-interval" + (interval=='0d'?" active ":"") + "' data-interval='0d'>1 day</button>" +
            "<button data-target='" + $id + "' class='btn btn-default dp-interval" + (interval=='7d'?" active ":"") + "' data-interval='7d'>1 week</button>" +
            "<button data-target='" + $id + "' class='btn btn-default dp-interval " + (interval=='1m'?" active ":"") + " ' data-interval='1m'>month</button>";
    }

    cityPicker("#departure-el", "#departure-dataholder", 'from');
    cityPicker("#destination-el", '#destination-dataholder', 'to');


    $('#swich-dep-dest').click(function () {
        var $departure2 = $("#departure-dataholder");
        var $fromdata = $departure2.data('data');
        var $destination = $('#destination-dataholder');
        var $todata = $destination.data('data');

        var $departure4 = $("#departure-el");
        var $select2datafrom = $departure4.data('selected');
        var $destination3 = $("#destination-el");
        var $select2datato = $destination3.data('selected');

        if($select2datafrom == undefined) $select2datafrom = [];
        if($select2datato == undefined) $select2datato = [];

        $departure4.data('selected', $select2datato);
        $destination3.data('selected', $select2datafrom);

        $departure2.data('data', $todata);
        $destination.data('data', $fromdata);

        var $fromhtml = $departure2.html();
        var $tohtml = $destination.html();

        $departure2.html($tohtml);
        $destination.html($fromhtml);


        var $departure3 = $departure4;
        if($todata.length > 0){
            $departure2.show();
            $departure2.prev().show();
            $departure3.prev().hide();
            $departure3.prev().prev().hide();
        }else{
            $departure2.hide();
            $departure2.prev().hide();
            $departure3.prev().show();
            $departure3.prev().prev().show();
        }

        var $destination2 = $destination3;
        if($fromdata.length > 0){
            $destination.show();
            $destination.prev().show();
            $destination2.prev().hide();
            $destination2.prev().prev().hide();
        }else{
            $destination.hide();
            $destination.prev().hide();
            $destination2.prev().show();
            $destination2.prev().prev().show();
        }

        return false;
    });

    // RANGE SLIDER(PRICE SLIDER)
    ////////////////////////////////////////////////////////////////////////////////////////////

    var $slider = $("#slider-range");
    $slider.slider({
        range: true,
        min: 0,
        max: 1000,
        values: [ 0, 700 ],
        slide: function( event, ui ) {
            $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
        }
    });
    $( "#amount" ).val( "$" + $slider.slider( "values", 0 ) + " - $" + $slider.slider( "values", 1 ) );

    //$slider.on( "slidechange", formChange );
    ////////////////////////////////////////////////////////////////////////////////////////////


    // RANGE SLIDER(PRICE SLIDER)
    ////////////////////////////////////////////////////////////////////////////////////////////

    var $sliderStopover = $("#slider-range-stopover");
    $sliderStopover.slider({
        range: true,
        min: 60,
        max: 2040,
        step: 60,
        values: [ 60, 2040 ],
        slide: function(e, ui) {
            var hoursF = Math.floor(ui.values[0] / 60);
            var hoursT = Math.floor(ui.values[1] / 60);
            $('#stopover').val(hoursF+"h - " + hoursT+'h');
        }
    });
    $( "#stopover" ).val($sliderStopover.slider( "values", 0 )/60 + "h - " + Math.floor($sliderStopover.slider( "values", 1 )/60) + "h" );

    //$slider.on( "slidechange", formChange );
    ////////////////////////////////////////////////////////////////////////////////////////////



    // CLOSE BUTTON
    ////////////////////////////////////////////////////////////////////////////////////////////
    var $city_picker_close = $(".city-picker-close");
    $city_picker_close.click(function () {
        var $body = $("body");
        var $height = $body.css("margin-top");
        $body.animate({"margin-top": 0}, 400);
        var $cityPicker = $(this).closest('.city-picker');
        $cityPicker.animate({"margin-top": -1 * $height}, 400, 'swing', function () {
            $cityPicker.css("margin-top", -9999);
        });
    });
    ////////////////////////////////////////////////////////////////////////////////////////////

    // DROPDOWN MENU BUTTON
    ////////////////////////////////////////////////////////////////////////////////////////////
    $(".city-picker-dropdown").click(cityPickerSlideDown);

    function cityPickerSlideDown() {
        var $body = $("body");

        //if body margin top > 0 then there is some list showing that we need to close
        var $bodyHeight = $body.css("margin-top");
        if(parseInt($bodyHeight) > 0){
            $body.css("margin-top", 0);
            $(".city-picker").css("margin-top", -9999);
        }


        var $this = $(this);
        var $cityPicker = $($($this).data('target'));

        var $height = parseInt($cityPicker.height());
        $height += parseInt($cityPicker.css("padding-top").replace("px", ""));
        $height += parseInt($cityPicker.css("padding-bottom").replace("px", ""));

        $cityPicker.css("margin-top", -2 * ($height));

        $body.animate({"margin-top": $height}, 400, 'swing', function () {
        });

        $cityPicker.animate({"margin-top" : 0}, 400, 'swing', function () {
            //var $cityPicker = $this.parent().find('.city-picker');

            if($this.attr('id') != 'edit-company')
                $cityPicker.find(".city-search").keyup();

            $('html, body').animate({scrollTop:0}, 'slow');
        });
    }
    ////////////////////////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////
    //PRE SELECT COMPANY
    var $edit = $("#edit-companies");
    if($(".company-name").length > 0){
        $edit.find("option").each(function () {
            if($(this).text().toLowerCase() == $(".company-name").text().toLowerCase()){
                $(this).attr('selected', true);
            }
        });
    }

    $(".company-info .company-clear").click(function () {
        //deselect all
        $("#edit-companies").val(null).trigger("change");
        //remove .company-info
        $(this).closest('.company-info').remove();
        //submit form
        formChange();
    });


    $edit.select2({placeholder: "Companies"});

    $city_picker.find(".check-all").hide();
    $city_picker.find('input[type=checkbox]').change(function () {
        if($(this).closest(".city-list").find(":checkbox:checked").length > 0){
            //show un-check all
            $(this).closest(".city-picker").find(".check-all").show();
        }else{
            //hide uncheck all
            $(this).closest(".city-picker").find(".check-all").hide();
        }

        //var $cityPicker = $(this).closest(".city-picker");
        //var $id = $cityPicker.attr('id');
        //var $list = $("span[data-target='#"+ $id +"']");

        //setTimeout(function () {
        //    fill_destination_country($list, $cityPicker);
        //}, 200);
    });


    var $toggle = $(".toggle");
    $toggle.each(function () {
        var $target = $(this).data('target');
        $($target).hide();
    });
    $toggle.click(function () {
        var $target = $(this).data('target');
        $(this).slideUp();
        $($target).slideToggle();
        return false;
    });


    //Pre Select current place
    //////////////////////////////////////////////////////////////////////////////////////////////
    var $to = $('.to-places:not(.ignore)');
    if($to.length > 0){
        $to.find('a, span:not(.glyphicon)').each(function () {
            var $name = $(this).text();

            var $destination = $("#destination-dataholder");

            var $id = $(this).data('id');
            if($(this).data('type') == 'country'){
                //country
                $id += "_";
            }

            $destination.dataHolder('add', {id: $id, text: $name});

            var $deparpureEl = $("#destination-el");
            $deparpureEl.data('selected', $destination.dataHolder('data'));

        });


    }else{
        //Pre Select from bread crumbs
        var $place = $('ol.breadcrumb').find('li.active').text();
        $("#dropdown-destination").find('.city-list').find("input[type='checkbox']").each(function () {
            if($(this).data('name') == $place){
                $(this).attr('checked', true).change();

                var $destination = $("#destination-dataholder");

                var $id = $(this).val();
                if($(this).data('type') == 'country'){
                    //country
                    $id += "_";
                }

                $destination.dataHolder('add', {id: $id, text: $(this).data('name')});

                var $deparpureEl = $("#destination-el");
                $deparpureEl.data('selected', $destination.dataHolder('data'));
            }
        });
    }




    //////////////////////////////////////////////////////////////////////////////////////////////

    $(".city-list .columnize").columnize({lastNeverTallest: true});


    //Pre Select departure
    var $from = $('.from-places');
    if($from.length > 0){
        var $link = $from.find('a, span').first();

        var $departure = $("#departure-dataholder");

        var $id = $link.data('id');
        if($($link).data('type') == 'country'){
            //country
            $id += "_";
        }
        var $name1 = $link.text();
        $departure.dataHolder('add', {id: $id, text: $name1});


        var $deparpureEl = $("#departure-el");
        $deparpureEl.data('selected', $departure.dataHolder('data'));

    }else{
        //Pre Select from preferred countries
        var $dropdown = $("#dropdown-departure");
        $dropdown.find('.city-list').find(".city-near").find("input[type='checkbox']").attr('checked', true).eq(0).change();
        $dropdown.find('.city-list').find(".city-near").find("input[type='checkbox']").each(function () {

            var $departure = $("#departure-dataholder");
            var $data = $departure.dataHolder('data');
            var $id = $(this).val();

            var add = true;
            if($(this).data('type') == 'country'){
                //country
                $id += "_";
            }else{
                //city
                //check parent
                var $countryId = $(this).closest('ul.city-near').find('input').first().val() + "_";

                for(var i = 0; i<$data.length; i++){
                    if($data[i] == $countryId){
                        add = false;
                        break;
                    }
                }
            }

            if(add){
                $departure.dataHolder('add', {id: $id, text: $(this).data('name')});

                var $deparpureEl = $("#departure-el");
                $deparpureEl.data('selected', $departure.dataHolder('data'));
            }

        });
    }




    ////////////////////////////////////////////////////////////////////////////////////////////////
    //SKYPICKER FETCH DATA
    var $travelbaseItems = $(".travelbase_items");
    $travelbaseItems.on('click', '.book-form', function (e) {
        e.preventDefault();
        e.stopPropagation();


        var $container = $(this).closest('tr');

        if(!$container.next().hasClass('form')){
            $.get('/book-form/', function (responce) {
                $container.after("<tr class='form'><td colspan='4'>"+ responce +"</td></tr>");
            });
        }else{
            $container.next().toggle();
        }
    });
    ///////////////////////////////////////////////////////////////////
    //PAY BUTTON
    $travelbaseItems.on('click', '.btn-pay', function (e) {
        e.preventDefault();
        e.stopPropagation();


        var $flightId = $(this).closest('tr').prev().data('flightid');

        var $form = $(this).closest('form');

        //check fields
        var errors = false;
        $form.find(":input").each(function () {
            if($(this).val() == ""){
                errors = true;
                $(this).closest("div.form-group").addClass("has-error");
            }
        });

        if(errors)
            return;

        var $pnum = $form.find('ul.passengers > li').length-1;

        var $bnum = 0;
        $form.find('ul.passengers').find('select[id$="bnum"]').find("option:selected").each(function () {
            $bnum += parseInt($(this).val());
        });

        $.getJSON("https://api.skypicker.com/api/v0.1/check_flights?flights="+$flightId+"&pnum="+$pnum+"&bnum="+$bnum+"&partner=twp", function (responce) {
            //noinspection JSUnresolvedVariable
            console.log(responce.flights_checked + " " + !responce.flights_invalid);
            //noinspection JSUnresolvedVariable
            if(responce.flights_checked && !responce.flights_invalid){
                //can book

                //noinspection JSUnresolvedVariable
                var price = responce.flights_price;

                $.getJSON('/book-form/?price='+price+"&flights="+$flightId+"&"+$form.serialize(), function (postData) {
                    console.log(postData);

                    if(postData.error){
                        alert(postData.msg);
                    }else{
                        $.post("https://api.skypicker.com/api/v0.1/save_booking?v=2", JSON.stringify(postData), function (responce) {
                            console.log(responce);
                            if(responce.status && responce.status == 'error'){
                                alert(responce.msg);
                            }
                        })
                    }


                });

            }else{
                //flight changed
            }
        });

        return false;
    });

    $travelbaseItems.on('click', '.skypicker-toggle', function () {
        //close other opened calendar
        $(".skypicker-dropdown").remove();

        //scroll to item
        $('html, body').animate({
            scrollTop: $(this).offset().top
        }, 1000);

        var $badge = $("#lowcost-badge");

        var $type = $(this).data('type'); //4 == oneway, 3 == with return

        if($(this).next().hasClass('skypicker-dropdown')){
            if($(this).next().next().hasClass('sp-show-more-wrapper')){
                $(this).next().next().remove();
            }
            //remove trip count from badge
            $badge.text(parseInt($badge.text()) - $(this).next().find('.trip').length);
            $(this).next().slideToggle().remove();
            return;
        }

        //show loading
        var $loading = $('.loading');
        $loading.show();
        var opts = {
            lines: 15, // The number of lines to draw
            length: 26, // The length of each line
            width: 2, // The line thickness
            radius: 36, // The radius of the inner circle
            corners: 0, // Corner roundness (0..1)
            rotate: 40, // The rotation offset
            direction: 1, // 1: clockwise, -1: counterclockwise
            color: '#000', // #rgb or #rrggbb or array of colors
            speed: 1, // Rounds per second
            trail: 66, // Afterglow percentage
            shadow: false, // Whether to render a shadow
            hwaccel: false, // Whether to use hardware acceleration
            className: 'spinner', // The CSS class to assign to the spinner
            zIndex: 2e9, // The z-index (defaults to 2000000000)
            top: '200px', // Top position relative to parent
            left: '50%' // Left position relative to parent
        };
        var spinner = new Spinner(opts).spin($loading.eq(1).find('.loading-container')[0]);




        var $tr = $(this);

        var $id = $(this).data('itemid');
        var $filter = getFilter();

        $filter.orderField = 'price';
        $filter.orderDirection = 'asc';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', "http://api.travelwebpartner.com/api/skypicker.fetch/" + $(this).data('from') + "/" + $(this).data('to'));
        //xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (this.readyState == 4) {

                var $eventSource = [];

                var $row = '<div class="row skypicker-dropdown">' +
                        '<div class="row">' +
                            '<div class="col-md-6"><h2>'+ trans.departures[$lang] +'</h2><div class="calendar-header-1"></div>' +
                                '<div class="btn-group">' +
                    '<button class="btn btn-primary calendar-navigate-1" data-calendar-nav="prev">&lt;&lt; ' + trans.prev[$lang] + '</button>' +
                    '<button class="btn calendar-navigate-1" data-calendar-nav="today">' + trans.today[$lang] + '</button>' +
                    '<button class="btn btn-primary calendar-navigate-1" data-calendar-nav="next">' + trans.next[$lang] + ' &gt;&gt;</button>' +
                    '</div>' +
                    '<div class="calendar calendar-1"></div>' +
                            '</div>';



                //if we have return date add second calendar
                var $date_end = $datepickTo.datepick('getDate');
                if($date_end.length > 0 && $type == 3){
                    $row += '<div class="col-md-6"><h2>'+ trans.returnDate[$lang] +'</h2><div class="calendar-header-2"></div>' +
                    '<div class="btn-group">' +
                    '<button class="btn btn-primary calendar-navigate-2" data-calendar-nav="prev">&lt;&lt; ' + trans.prev[$lang] + '</button>' +
                    '<button class="btn calendar-navigate-2" data-calendar-nav="today">' + trans.today[$lang] + '</button>' +
                    '<button class="btn btn-primary calendar-navigate-2" data-calendar-nav="next">' + trans.next[$lang] + ' &gt;&gt;</button>' +
                    '</div>' +
                    '<div class="calendar calendar-2"></div>' +
                    '</div>';
                }

                $row += '</div>' +
                '<div class="col-xs-12">';

                var $data = JSON.parse(this.responseText);


                var $user = $("body").data('user');
                if($user) $user = "&user=" + $user;
                else $user = "";

                for(var i = 0; i< $data.length; i++){

                    if($filter.sameDay == 1){
                        //noinspection JSUnresolvedVariable
                        if($data[i].dDate != $data[i].aDate){
                            continue;
                        }
                    }

                    //noinspection JSUnresolvedVariable
                    var $event = {
                        "id": i,
                        "title" : $data[i].price,
                        "url" : $data[i].deep_link,
                        "class": "event-success",
                        "start": $data[i].dTimestamp * 1000, // Milliseconds
                        "end": $data[i].dTimestamp * 1000, // Milliseconds
                        "date": $data[i].dDate
                    };


                    //add only events with lowest prices
                    var found = false;
                    for(var k =0;k<$eventSource.length; k++){
                        //noinspection JSUnresolvedVariable
                        if($eventSource[k].date == $data[i].dDate){
                            found = true;
                            if(parseFloat($eventSource[k].title) > parseFloat($data[i].price)){
                                $eventSource[k] = $event;
                                break;
                            }
                        }
                    }
                    //add new events
                    if(!found){
                        $eventSource.push($event);
                    }

                    //noinspection JSUnresolvedVariable
                    var stops = $data[i].route.length - 1 ;
                    if(stops == 0) stops = "";
                    else stops += " stops";

                    //noinspection JSUnresolvedVariable
                    var $date = $data[i].dDate.slice(0, 6) + $data[i].dDate.slice(8, $data[i].dDate.length);

                    //noinspection JSUnresolvedVariable
                    var $mysqlDate = (new Date($data[i].dTimestamp * 1000)).toMysqlFormat();

                    $row +=
                        '<div class="trip row" data-date="'+ $mysqlDate +'" data-direction="to">' +
                        '    <div class="col-xs-1 trip-duration">'+$date+'</div>' +
                        '    <div class="col-xs-8 trip-path">';

                    $row += '<table><tr>';

                    $row += itemsToHtml($data, i);

                    $row += '</tr></table>';

                    //noinspection JSUnresolvedVariable
                    $row +=
                        '    </div>' +
                        '    <div class="col-xs-1 trip-duration nowrap">' + $data[i].fly_duration +'<br/>'+stops+'</div>' +
                        '<div class="col-xs-2 trip-cost text-success">' +
                        '        <p>'+$data[i].price+'€</p>' +
                        '        <a class="btn btn-info trip-btn-cost" target="_blank" href="http://api.travelwebpartner.com/away/?url=' + $data[i].deep_link + '?partner=' + window.location.hostname + $user + '">'+$data[i].price+'€</a>' +
                        '        <button class="btn btn-danger trip-btn-close">close</button>' +
                        '    </div>' +
                        '</div>';

                }

                $row += '</div></div>';

                //if($data.length > 10){
                //    //add load more button
                //    $row += "<div class='row sp-show-more-wrapper'><a class='btn btn-default sp-show-more col-xs-12' href='#'>Show more</a></div>";
                //}

                $tr.after($row);

                //hide all trips and show only first ten
                $(".skypicker-dropdown .trip").hide();
                //$(".skypicker-dropdown .trip:lt(10)").show();

                //add trips to badge
                $badge.text(parseInt($badge.text()) + $data.length);

                //hide loading
                spinner.stop();


                //noinspection JSUnresolvedVariable,JSUnusedLocalSymbols
                var calendar1 = $(".calendar-1").calendar(
                    {
                        tmpl_path: "/bundles/sandboxwebsite/frontend/js/calendar/tmpls/",
                        events_source: $eventSource,
                        onAfterViewLoad: function(view) {
                            $('.calendar-header-1').text(this.getTitle());
                            var $cheapest = null;
                            $('.calendar-1 .events-list').each(function () {
                                if(!$cheapest) $cheapest = $(this);

                                if(parseInt($cheapest.text(), 10) > parseInt($(this).text(), 10)){
                                    $cheapest = $(this);
                                }
                            });

                            if($cheapest) $cheapest.parent().addClass('cal-item-cheapest');
                        },
                        day: (new Date($data[Math.floor($data.length / 2)].dTimestamp * 1000)).toMysqlFormat()
                    });

                calendar1.setLanguage($lang);
                calendar1.view();

                $(".calendar-navigate-1").click(function () {
                    calendar1.navigate($(this).data('calendar-nav'));
                });

                $('.skypicker-dropdown').on('click', '.calendar-1 .cal-month-day, .calendar-1 .cal-year-box .span3', function () {
                    var $date = $(this).children('[data-cal-date]').data('cal-date');

                    var modal = $("#sp-modal");
                    modal.modal('show');
                    modal.find("#modal-content").html("");
                    $(".skypicker-dropdown .trip").hide().each(function () {
                        if($(this).data('date') == $date && $(this).data('direction') == 'to'){

                            modal.find("#modal-content").append($(this).clone());
                            //$(this).slideDown('fast');
                        }
                    });
                    modal.find(".trip").show();
                });

                $('.loading').hide();
            }
        };
        xhr.send(JSON.stringify($filter));


        if($type == 3){
            var xhr2 = new XMLHttpRequest();
            xhr2.open('POST', "http://api.travelwebpartner.com/api/skypicker.fetch/" + $(this).data('to') + "/" + $(this).data('from'));
            //xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
            xhr2.onreadystatechange = function () {
                if (this.readyState == 4) {

                    var $eventSource = [];

                    var $row = '';

                    var $data = JSON.parse(this.responseText);

                    for(var i = 0; i< $data.length; i++){

                        if($filter.sameDay == 1){
                            //noinspection JSUnresolvedVariable
                            if($data[i].dDate != $data[i].aDate){
                                continue;
                            }
                        }

                        //noinspection JSUnresolvedVariable
                        var $event = {
                            "id": i,
                            "title" : $data[i].price,
                            "url" : $data[i].deep_link,
                            "class": "event-return",
                            "start": $data[i].dTimestamp * 1000, // Milliseconds
                            "end": $data[i].dTimestamp * 1000, // Milliseconds
                            "date": $data[i].dDate
                        };


                        //add only events with lowes prices
                        var found = false;
                        for(var k =0;k<$eventSource.length; k++){
                            //noinspection JSUnresolvedVariable
                            if($eventSource[k].date == $data[i].dDate){
                                found = true;
                                if(parseFloat($eventSource[k].title) > parseFloat($data[i].price)){
                                    $eventSource[k] = $event;
                                    break;
                                }
                            }
                        }
                        //add new events
                        if(!found){
                            $eventSource.push($event);
                        }

                        //noinspection JSUnresolvedVariable
                        var stops = $data[i].route.length - 1 ;
                        if(stops == 0) stops = "";
                        else stops += " stops";

                        //noinspection JSUnresolvedVariable
                        var $date = $data[i].dDate.slice(0, 6) + $data[i].dDate.slice(8, $data[i].dDate.length);

                        //noinspection JSUnresolvedVariable
                        var $mysqlDate = (new Date($data[i].dTimestamp * 1000)).toMysqlFormat();

                        $row +=
                            '<div class="trip row" data-date="'+ $mysqlDate +'" data-direction="from">' +
                            '    <div class="col-xs-1 trip-duration">'+$date+'</div>' +
                            '    <div class="col-xs-8 trip-path">';

                        $row += '<table><tr>';

                        $row += itemsToHtml($data, i);

                        $row += '</tr></table>';

                        //noinspection JSUnresolvedVariable
                        $row +=
                            '    </div>' +
                            '    <div class="col-xs-1 trip-duration nowrap">' + $data[i].fly_duration +'<br/>'+stops+'</div>' +
                            '<div class="col-xs-2 trip-cost text-success">' +
                            '        <p>'+$data[i].price+'€</p>' +
                            '        <button class="btn btn-info trip-btn-cost">'+$data[i].price+'€</button>' +
                            '        <button class="btn btn-danger trip-btn-close">close</button>' +
                            '    </div>' +
                            '</div>';

                    }

                    //if($data.length > 10){
                    //    //add load more button
                    //    $row += "<div class='row sp-show-more-wrapper'><a class='btn btn-default sp-show-more col-xs-12' href='#'>Show more</a></div>";
                    //}

                    $tr.next('.skypicker-dropdown').find("> .col-xs-12").append($row);

                    //hide all trips and show only first ten
                    $(".skypicker-dropdown .trip").hide();
                    //$(".skypicker-dropdown .trip:lt(10)").show();

                    //add trips to badge
                    $badge.text(parseInt($badge.text()) + $data.length);

                    //hide loading
                    spinner.stop();

                    //noinspection JSUnresolvedVariable,JSUnusedLocalSymbols
                    var calendar2 = $(".calendar-2").calendar(
                        {
                            tmpl_path: "/bundles/sandboxwebsite/frontend/js/calendar/tmpls/",
                            events_source: $eventSource,
                            onAfterViewLoad: function(view) {
                                $('.calendar-header-2').text(this.getTitle());
                                var $cheapest = null;
                                $('.calendar-2 .events-list').each(function () {
                                    if(!$cheapest) $cheapest = $(this);

                                    if(parseInt($cheapest.text(), 10) > parseInt($(this).text(), 10)){
                                        $cheapest = $(this);
                                    }
                                });

                                if($cheapest) $cheapest.parent().addClass('cal-item-cheapest');
                            },
                            day: (new Date($data[Math.floor($data.length / 2)].dTimestamp * 1000)).toMysqlFormat()
                        });

                    calendar2.setLanguage($lang);
                    calendar2.view();

                    $(".calendar-navigate-2").click(function () {
                        calendar2.navigate($(this).data('calendar-nav'));
                    });


                    $('.skypicker-dropdown').on('click', '.calendar-2 .cal-month-day, .calendar-2 .cal-year-box .span3', function () {
                        var $date = $(this).children('[data-cal-date]').data('cal-date');

                        var modal = $("#sp-modal");
                        modal.modal('show');
                        modal.find("#modal-content").html("");

                        $(".skypicker-dropdown .trip").hide().each(function () {
                            if($(this).data('date') == $date && $(this).data('direction') == 'from'){
                                modal.find("#modal-content").append($(this).clone());
                                //$(this).slideDown('fast');
                            }
                        });
                        modal.find(".trip").show();
                    });

                    $('.loading').hide();
                }
            };

            setTimeout(function () {

                //change filter dates
                //to send second request
                //dest and dep are in link
                //date.start = return from
                //date.end = return to

                //noinspection JSUnresolvedFunction
                var $date_end = $datepickTo.datepick('getDate');
                if($date_end.length > 0){
                    //noinspection JSUnresolvedVariable
                    $filter.date.start = $.datepick.formatDate('yyyy-mm-dd', $date_end[0]);
                    //noinspection JSUnresolvedVariable
                    $filter.date.end = $.datepick.formatDate('yyyy-mm-dd', $date_end[1]);
                    $filter.date.returnFrom = null;
                    $filter.date.returnTo = null;
                    //var $d = $date_end;
                    //$filter.date.start = $d.substr(6, 4) + "-" + $d.substr(3, 2) + "-" + $d.substr(0, 2) ;
                }
                //var $date = new Date($filter.date.start);
                //$date.setMonth($date.getMonth() + 1);
                //$filter.date.end = $date.toMysqlFormat();
                //
                //$filter.date.return = null;
                xhr2.send(JSON.stringify($filter));
            }, 1000);

        }

    });
    ////////////////////////////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////////////////////////////
    function itemsToHtml($data, i){
        var $row = '';
        //noinspection JSUnresolvedVariable
        for(var j=0; j<$data[i].route.length; j++){
            //noinspection JSUnresolvedVariable
            var duration = ($data[i].route[j].aTimeUTC - $data[i].route[j].dTimeUTC) / 60 ;//minutes
            if(duration >= 60){
                var $hours = Math.floor(duration / 60 );//hours
                var $mins = duration - $hours*60;

                if($mins == 0){
                    duration = $hours + 'h';
                }else if($mins < 10){
                    duration = $hours  + ":0" + $mins + 'h';
                }else{
                    duration = $hours  + ":" + $mins + 'h';
                }

            }else{
                duration += "min";
            }


            var $time = "";

            if(j == 0){
                //noinspection JSUnresolvedVariable
                $time = $data[i].route[j].dTime;
            }
            else if (j == $data[i].route.length-1){
                //noinspection JSUnresolvedVariable
                $time = $data[i].route[j].aTime;
            }
            else if($data[i].route[j+1]) {//if has more put next depart time
                //noinspection JSUnresolvedVariable
                $time = $data[i].route[j-1].aTime + " --- " + $data[i].route[j].dTime
            }



            //noinspection JSUnresolvedVariable
            $row +=
                '<td width="1%"><div class="trip-path-point">' +
                '            <div class="trip-path-point-airport">'+$data[i].route[j].flyFrom+'</div>' + //cityFrom
                '            <div class="trip-path-point-time">'+$data[i].route[j].dTime+ '</div>' +
                '</div></td>';

            //noinspection JSUnresolvedVariable
            $row +=
                '<td><div class="trip-path-spacer">' +
                '            <div class="trip-path-spacer-label"><span data-original-title="'+$data[i].route[j].airline+'" data-toggle="tooltip" class="airline" style="background: url(&quot;/bundles/sandboxwebsite/img/airlines/'+$data[i].route[j].airline+'.gif&quot;) no-repeat scroll 0% 0% transparent;"></span>'+duration+'</div>' +
                '            <div class="trip-path-spacer-arrow-wrapper trip-path-spacer-arrow-wrapper-init" style="width: 100%;">' +
                '                                        <span class="trip-path-spacer-line">' +
                '                                            <div></div>' +
                '                                        </span>' +
                '                <span class="trip-path-spacer-arrow"></span>' +
                '            </div>' +
                '        </div></td>';

            //noinspection JSUnresolvedVariable
            $row +=
                '<td width="1%"><div class="trip-path-point">' +
                '            <div class="trip-path-point-airport">'+$data[i].route[j].flyTo+'</div>' + //cityTo
                '            <div class="trip-path-point-time">'+$data[i].route[j].aTime+

                '</div>' +
                '        </div></td>';

            //noinspection JSUnresolvedVariable
            if($data[i].route[j+1]){//if has more put spacer

                //noinspection JSUnresolvedVariable
                var durationWait = ( $data[i].route[j+1].dTimeStamp - $data[i].route[j].aTimeStamp) / 60 ;//minutes
                if(durationWait > 60){
                    $hours = Math.floor(durationWait / 60 );//hours
                    $mins = durationWait - $hours*60;

                    if($mins == 0){
                        durationWait = $hours + 'h';
                    }else if($mins < 10){
                        durationWait = $hours  + ":0" + $mins + 'h';
                    }else{
                        durationWait = $hours  + ":" + $mins + 'h';
                    }

                }else{
                    durationWait += "min";
                }

                $row +=
                    '<td><div class="trip-path-spacer">' +
                    '            <div class="trip-path-spacer-label">'+durationWait+'</div>' +
                    '            <div class="trip-path-spacer-arrow-wrapper trip-path-spacer-arrow-wrapper-init trip-path-spacer-arrow-layover" style="width: 100%;">' +
                    '                                        <span class="trip-path-spacer-line">' +
                    '                                            <div></div>' +
                    '                                        </span>' +
                    '                <span class="trip-path-spacer-arrow"></span>' +
                    '            </div>' +
                    '        </div></td>';
            }

        }

        return $row;
    }


    //////////////////////////////////////////////////////////////////////////////////////////////////
    //SKYPICKER show more trips
    $(".travelbase_items_sp").on('click', '.sp-show-more', function (e) {
        e.preventDefault();
        var $dropDown = $(this).closest(".travelbase_items_sp").find(".skypicker-dropdown");
        $dropDown.find(".trip:hidden:lt(10)").show();
        if($dropDown.find(".trip:hidden").length == 0){
            $(this).hide();
        }
    });

    //////////////////////////////////////////////////////////////////////////////////////////////////
    //SKYPICKER show route details
    $travelbaseItems.on('click', ".skypicker-route-details-toggle", function () {
        $(this).next(".skypicker-route-details").slideToggle();
    });
    //////////////////////////////////////////////////////////////////////////////////////////////////

    $(".city-search").keyup(function () {
        var $value = $(this).val().trim();
        var $cityList = $(this).closest(".city-picker").find('.city-list');


        if($cityList.find(":checkbox:checked").length > 0 || $value.length > 0){
            //show un-check all
            $(this).closest(".city-picker").find(".check-all").show();
        }else {
            //hide un-check all
            $(this).closest(".city-picker").find(".check-all").hide();
        }

        if ($value == "" || $value.length < 3) {
            $cityList.find('i').show();
            $cityList.find("ul").show();
            $cityList.find("li").show();
            $cityList.find('i').removeClass('fa-angle-up').addClass('fa-angle-down');
            $("li.parent-list ul").hide();
        } else {
            $cityList.find('i').hide();
            $cityList.find(":checkbox").each(function () {
                var $cityName = $(this).data('name') + "";
                if ($cityName.toLowerCase().indexOf($value.toLowerCase()) >= 0) {
                    //show
                    $(this).closest("li").show();
                    //check parent
                    var $li = $(this);
                    while ($li.closest("ul").first().closest("li").closest("ul").find(' > li').first().length > 0) {
                        $li.closest('ul').show();
                        $li.closest("ul").first().closest("li").closest("ul").find(' > li').first().show();
                        $li = $li.closest("ul").first().closest("li").closest("ul").find(' > li').first();
                    }
                } else {
                    //$(this).closest('ul').hide();
                    $(this).closest("li").hide();
                }

                if ($value == "") {
                    $("li.parent-list ul").hide();
                }
            });
        }

        if ($cityList.find("li:visible").length == 0) {
            $cityList.find('.no-cities').show();
        } else {
            $cityList.find('.no-cities').hide();
        }

        if ($(this).closest(".form-item-destination-country").length > 0) {
            //body top margin!!
            var $cityPicker = $(this).closest(".city-picker");
            var $height = parseInt($cityPicker.height());
            $height += parseInt($cityPicker.css("padding-top").replace("px", ""));
            $height += parseInt($cityPicker.css("padding-bottom").replace("px", ""));

            $("#body").css("margin-top", $height);
            $cityPicker.css("margin-top", -1 * ($height));
        }
        //type filters
        if($(this).closest(".city-filters").find(".city-type-btn:checked").length > 0)
            $(this).closest(".city-picker").find('.city-type-btn').change();

    });


    //city type filter
    $('.city-type-btn').change(cityFilter);


    //TAXONOMY CONTENT TYPE FILTER SUBMIT ON CHANGE
    $("#block-system-main").on('change', ':input', function () {
        $(this).closest("form").find("#edit-submit-taxonomy-term").click();
    });


    ////////////////////////////////////////////////////////////////////////////////
    //FANCYBOX
    $(".image-img > a").fancybox({
        prevEffect	: 'none',
        nextEffect	: 'none',
        helpers	: {
            title: {
                type: 'inside',
                position: 'top'
            },
            thumbs	: {
                width	: 50,
                height	: 50
            }
        }
    });
    /////////////////////////////////////////////////////////////////////////////////


    $("#form-submit").click(function (e) {
        e.preventDefault();

        $('a[data-type="flight-hotel"]').tab('show');

        formChange(true);
    });//.click();
    formChange(false);

    $("#flyWithReturn, #flyOneWay, #sameDay, #directFlight").change(function () {
        formChange();
    });
    //getTable();
    //setTimeout(function () {
    //    getTable(".travelbase_items_sp");
    //}, 1000);

});

function cityFilter(){
    //get search text field
    var $search = $(this).closest('.city-filters').find(".city-search").val().trim();
    if($search.length < 3) $search = "";

    var $cityList = $(this).closest('.city-picker').find('.city-list');
    //get all checked filters
    var $filters = [];
    $(this).closest(".city-filters").find(".city-type-btn:checked").each(function () {
        $filters.push($(this).data('type'));
    });
    var $found = false;
    if($filters.length > 0) {
        $(this).closest('.city-picker').find('.city-list').find(":checkbox").each(function () {
            var $cityTypes = $(this).data('city-type');

            $found = false;
            $.each($filters, function (index, value) {
                if(value && $.inArray(value, $cityTypes) != -1)
                    $found = true;
            });


            //check for text search
            if($search){
                var $cityName = $(this).data('name') + "";
                if($cityName.toLowerCase().indexOf($search.toLowerCase()) < 0){
                    $found = false;
                }
            }
            //if found
            if ($found) {


                $(this).closest('li').show();

                var $li = $(this);
                while ($li.closest("ul").first().closest("li").closest("ul").find(' > li').first().length > 0) {
                    $li.closest('ul').show();
                    $li.closest("ul").first().closest("li").closest("ul").find(' > li').first().show();
                    $li = $li.closest("ul").first().closest("li").closest("ul").find(' > li').first();
                }
            } else {
                $(this).closest('li').hide();
            }
        });

        if ($cityList.find("li:visible").length == 0) {
            $cityList.find('.no-cities').show();
        } else {
            $cityList.find('.no-cities').hide();
        }

    }else{
        //show all
        $cityList.find('ul, li').show();
        $cityList.find("li.parent-list ul").hide();
        $cityList.find('.no-cities').hide();
        $cityList.find('i').removeClass('fa-angle-up').addClass('fa-angle-down');

        //check text filter
        $(this).closest('.city-filters').find(".city-search").keyup();
    }

    var $cityPicker = $(this).closest(".city-picker");
    var $height = parseInt($cityPicker.height());
    $height += parseInt($cityPicker.css("padding-top").replace("px", ""));
    $height += parseInt($cityPicker.css("padding-bottom").replace("px", ""));

    $("body").css("margin-top", $height);
    //$cityPicker.css("margin-top", -1 * ($height));
}

//noinspection JSUnusedLocalSymbols
function formChange(expand){
    getTable('.travelbase_items_df', true, expand);
    setTimeout(function () {
        getTable(".travelbase_items_sp", true, expand);
    }, 1000);

    //skyPickerImport();
}

//noinspection JSUnusedGlobalSymbols
function skyPickerImport(){
    var $progressbar = $(".progressbar");
    $progressbar.show();
    $progressbar.progressbar({
        value: false
    });
    var $filter = getFilter();
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'http://api.travelwebpartner.com/api/skypicker.import/');
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (this.readyState == 4) {
            var $data = JSON.parse(this.responseText);
            if($data.status == 1){
                getTable();
                $( ".progressbar").hide();

            }
        }
    };
    xhr.send(JSON.stringify($filter));
}

function getTable(container, reimport, expand){
    $("#form-submit").addClass('disabled');
    if(!container) container = ".travelbase_items:visible";
    var type = 1;
    if(reimport === false) type = 2;

    var $filter = getFilter(container);

    var $travelbase_items = $(container);
    //start loading
    var $badgeLoading = $($travelbase_items.data('badge') + "-loading");


    var opts = {
        lines: 11, // The number of lines to draw
        length: 4, // The length of each line
        width: 2, // The line thickness
        radius: 1, // The radius of the inner circle
        corners: 0, // Corner roundness (0..1)
        rotate: 40, // The rotation offset
        direction: 1, // 1: clockwise, -1: counterclockwise
        color: '#000', // #rgb or #rrggbb or array of colors
        speed: 1, // Rounds per second
        trail: 66, // Afterglow percentage
        shadow: false, // Whether to render a shadow
        hwaccel: false, // Whether to use hardware acceleration
        className: 'spinner', // The CSS class to assign to the spinner
        zIndex: 2e9, // The z-index (defaults to 2000000000)
        top: '50%', // Top position relative to parent
        left: '50%' // Left position relative to parent
    };

    $($travelbase_items.data('badge')).hide();
    var spinner = new Spinner(opts).spin($badgeLoading[0]);


    var opts2 = {
        lines: 15, // The number of lines to draw
        length: 26, // The length of each line
        width: 2, // The line thickness
        radius: 36, // The radius of the inner circle
        corners: 0, // Corner roundness (0..1)
        rotate: 40, // The rotation offset
        direction: 1, // 1: clockwise, -1: counterclockwise
        color: '#000', // #rgb or #rrggbb or array of colors
        speed: 1, // Rounds per second
        trail: 66, // Afterglow percentage
        shadow: false, // Whether to render a shadow
        hwaccel: false, // Whether to use hardware acceleration
        className: 'spinner', // The CSS class to assign to the spinner
        zIndex: 2e9, // The z-index (defaults to 2000000000)
        top: '200px', // Top position relative to parent
        left: '50%' // Left position relative to parent
    };
    var spinner2 = null;

    //show loading
    var $loading = $('.loading');
    if($(container).hasClass('travelbase_items_sp')){
        spinner2 = new Spinner(opts2).spin($loading.eq(1).find('.loading-container')[0]);
        $loading = $($loading[1]);
    }else{
        spinner2 = new Spinner(opts2).spin($loading.eq(0).find('.loading-container')[0]);
        $loading = $($loading[0]);
    }
    $loading.show();

    $.post('/api-filter/' + type, JSON.stringify($filter), function (responce) {

        $travelbase_items.html(responce.html);

        if($(container).hasClass('travelbase_items_sp')){
            var $skypicker = $(".skypicker-toggle");
            if($(container).find('.trip').length == 1 && $skypicker.length == 1){
                $skypicker.click();
                $(container).find('> div').css('height', 'auto');
                //$skypicker.hide();
            }
        }

        spinner.stop();
        //if(responce.total > 0 && $travelbase_items.data('badge'))
            $($travelbase_items.data('badge')).show().text(responce.total);

        $travelbase_items.find('.table-header a').click(function(){
            var $field = $(this).data('field');
            var $form = $('#travelbase-form');
            $form.find('input[name="order_field"]').val($field);

            var $direction = $form.find('input[name="order_direction"]');
            var  $directionVal = $(this).data('order');
            //if($(this).data('order') == 'asc') $directionVal = 'desc';
            //else $directionVal = 'asc';

            //$(this).data('order', $directionVal);
            $direction.val($directionVal);
            getTable(null, false);
            //$("#edit-companies").change();
            return false;
        });

        if($('#hot-offers').is(':visible') || $('#lowcost').is(':visible')){
            if(container == ".travelbase_items_sp"){
                if($(".travelbase_items:visible").find("> div").find(">div ").length == 0){
                    if($('.travelbase_items_df').find('> div').find(">div ").length > 1){
                        $("#travelbase_tabs").find("a:eq(0)").tab("show");
                    }else if($('.travelbase_items_sp').find('> div').find(">div ").length > 1){
                        $("#travelbase_tabs").find("a:eq(1)").tab("show");
                    }
                }
            }
        }

        //if(responce.total == 0){
            //check both tables
            //$('.travelbase_items').each(function (index) {
            //    if($(this).find('> div').find(">div ").length > 1){
            //        $("#travelbase_tabs").find("a:eq("+index+")").tab("show");
            //    }
            //});
        //}

        $("#form-submit").removeClass('disabled');

        //hide loading
        spinner2.stop();
        $loading.hide();

        if(expand === true || reimport === false)
            $('#loadMore').click();
    });
}


function cityPicker($el, $holder, $direction) {
    $($el).removeClass('hide');

    $($holder).dataHolder({
        afterRemove: function () {
            var $data = this.dataHolder('data');
            var $elem = $(this.data('target'));
            $elem.data('selected', $data);
            $elem.select2("val", "");
        }
    });

    var $lang = $("body").data('lang');
    if($lang == 'ee') $lang = 'et';

    $lang = $lang.charAt(0).toUpperCase() + $lang.slice(1);

    var $locale = $lang;
    if($locale == "En") $locale = "";
    if($locale == "Et") $locale = "Ee";

    var airportName = 'airportNameEn';
    if($locale == "Ee") airportName = 'airportNameEt';

    function repoFormatResult(repo) {
        var $title = repo['cityName' + $lang];
        //noinspection JSUnresolvedVariable
        if(repo.airportNameEn){
            $title += " <span class='text-muted'>("+repo[airportName];
            //noinspection JSUnresolvedVariable
            if(repo.airportCode.length < 4) {
                //noinspection JSUnresolvedVariable
                $title += ", " + repo.airportCode;
            }
            $title += ")</span>";
        }else{
            //noinspection JSUnresolvedVariable
            if(repo.airportCode.length < 4) {
                //noinspection JSUnresolvedVariable
                $title += " <span class='text-muted'>(" + repo.airportCode + ")</span>";
            }
        }

        $title = '<div class="col-xs-11 col-xs-offset-1">' + $title +'</div>';

        if(repo.id.toString().indexOf("_") != -1){
            $title = '<div class="col-xs-12"><strong>' + repo['countryName' + $locale] +'</strong></div>';
        }

        var markup = '<div class="row">' + $title;

        markup += '</div>';

        return markup;
    }

    function repoFormatSelection(repo) {
        //var $title = repo.countryName + "/" + repo.cityNameEn;
        var $title = repo['cityName' + $lang];

        if(repo.id.toString().indexOf("_") != -1){
            $title = repo['countryName' + $locale];
        }
        repo.text = $title;

        $($holder).dataHolder('add', repo);

        return $title;
    }

    function convertData(data){
        var $finalData = [];

        var data = $.map(data, function(el) {
                //el.id = Object.keys(el)[0];
                return el;
            }
        );

        var selected = $($el).data('selected');
        if(!selected) selected = [];

        //loop countries
        for(var i=0; i<data.length; i++){
            var $cityArr = data[i];

            //loop cities
            for(var j=0; j<Object.keys($cityArr).length; j++){
                if(j == 0){
                    //add county
                    var $country = $cityArr[Object.keys($cityArr)[j]];
                    $country = JSON.parse(JSON.stringify($country));
                    $country.id = $country.id + "_";

                    if(selected.indexOf($country.id) != -1){
                        $country.disabled = true;
                    }
                    $finalData.push($country);
                }
                //add city
                var $city = $cityArr[Object.keys($cityArr)[j]];
                $city = JSON.parse(JSON.stringify($city));
                if(selected.indexOf($city.id.toString()) != -1){
                    $city.disabled = true;
                }
                $finalData.push($city);
            }
        }
        return $finalData;
    }

    var $language = $('body').data('lang');
    var $trans = window.trans.searchForPlace[$language];

    if($direction){
        if($direction == 'from'){
            $trans = window.trans.searchFromPlace[$language];
        }else{
            $trans = window.trans.searchForPlace[$language];
        }
    }

    $($el).select2({
        placeholder: $trans,
        minimumInputLength: 3,
        multiple: true,
        width: '100%',
        dropdownAutoWidth: true,
        cacheDataSource: [],
        query: function(query) {
            //noinspection JSUndeclaredVariable
            self = this;
            var key = query.term;
            var cachedData = self.cacheDataSource[key];

            if(cachedData) {
                query.callback({results: convertData(cachedData)});
                return;
            } else {
                $.ajax({
                    url: 'http://api.travelwebpartner.com/api/city.findByText/',
                    data: { q : query.term },
                    dataType: 'json',
                    type: 'GET',
                    success: function(data) {
                        self.cacheDataSource[key] = data;
                        query.callback({results: convertData(data)});
                    }
                })
            }
        },
        initSelection: function (element, callback) {
            // the input tag has a value attribute preloaded that points to a preselected repository's id
            // this function resolves that id attribute to an object that select2 can render
            // using its formatResult renderer - that way the repository name is shown preselected
            $(element).val("");
            var id = $(element).val();
            id = '';
            if (id !== "") {
//                                        id = 'estonia';
                $.ajax("http://api.travelwebpartner.com/api/city.findByText/?q=" + id, {
                    dataType: "json"
                }).done(function (data) {

                    callback(convertData(data));
                });
            }
        },
        formatResult: repoFormatResult, // omitted for brevity, see the source of this page
        formatSelection: repoFormatSelection, // omitted for brevity, see the source of this page
        dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
        escapeMarkup: function (m) {
            return m;
        } // we do not want to escape markup since we are displaying html in results
    });

    $($el).on("change", function (e) {
        var $elem = $($el);
        var $selected = $elem.data('selected');
        if (!$selected) $selected = [];
        if ($selected.indexOf(e.val[0]) == -1) {
            $selected.push(e.val[0]);
        }
        $elem.data('selected', $selected);
        $elem.select2("val", "");
    });


    $($el).on('select2-blur', function () {
        if($($holder).dataHolder('data').length > 0){
            $($el).prev().hide();//slideUp();
            $($el).prev().prev().hide();//slideUp();

            var holder = $($holder);
            if(!holder.hasClass('placeholder-collapse')){
                holder.addClass('placeholder-collapse');
            }
        }
    });
}

function fixDiv() {
    var $container = $('.loading-container:visible');
    var $width = $container.parent().width();
    var $height = $container.parent().height();
    if ($(window).scrollTop() > 350 && $(window).scrollTop() < $height) {
        $container.css({
            'position': 'fixed',
            'top': '50px',
            'width' : $width
        });
    }
    else {
        $container.css({
            'position': 'relative',
            'top': 'auto',
            'width' : 'auto'
        });
    }
}
$(window).scroll(fixDiv);
fixDiv();