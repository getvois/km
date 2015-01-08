var $api_url = 'http://api.travel.markmedia.fi/api/item.filter/';
//var $api_url = 'http://travelbase.dev/app_dev.php/api/item.filter/'; //todo kosmos remove this
$(document).ready(function() {
    //setField(); //pre fill destination field
    var $form = $("#travelbase-form");
    $form.submit(function () {
        return false;
    });
    var $city_picker = $(".city-picker");


    $(".date").datepicker({ dateFormat: "dd.mm.yy" });
    $("#edit-date-start-datepicker-popup-0, #edit-date-end-datepicker-popup-0").datepicker( "option", "minDate", new Date() );
    $("#edit-date-start-datepicker-popup-0").datepicker( "option", "onSelect", function (date) {
        $("#edit-date-end-datepicker-popup-0").datepicker( "option", "minDate", date );
        //formChange();
    } );
    $("#edit-date-end-datepicker-popup-0").datepicker( "option", "onSelect", function (date) {
        //formChange();
    } );


    cityPicker("#departure-el", '#departure-selected');
    cityPicker("#destination-el", '#destination-selected');

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
    if($(".company-name").length > 0){
        $("#edit-companies option").each(function () {
            if($(this).text().toLowerCase() == $(".company-name").text().toLowerCase()){
                $(this).attr('selected', true);
            }
        });
    }


    $("#edit-companies").select2({placeholder: "Companies"});

    $city_picker.find(".check-all").hide();
    $city_picker.find('input[type=checkbox]').change(function () {
        if($(this).closest(".city-list").find(":checkbox:checked").length > 0){
            //show un-check all
            $(this).closest(".city-picker").find(".check-all").show();
        }else{
            //hide uncheck all
            $(this).closest(".city-picker").find(".check-all").hide();
        }

        var $cityPicker = $(this).closest(".city-picker");
        var $id = $cityPicker.attr('id');
        var $list = $("span[data-target='#"+ $id +"']");

        setTimeout(function () {
            fill_destination_country($list, $cityPicker);
        }, 200);
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
    var $to = $('.to-places');
    if($to.length > 0){
        $to.find('a').each(function () {
            var $name = $(this).text();
            $("#dropdown-destination").find('.city-list').find("input[type='checkbox']").each(function () {
                if($(this).data('name') == $name){
                    $(this).attr('checked', true).change();


                    var $destination = $("#destination-selected");
                    var $data = $destination.select2('data');

                    var $id = $(this).val();

                    var add = true;
                    if($(this).data('type') == 'country'){
                        //country
                        $id += "_";
                    }else{
                        //city
                        //check parent
                        var $countryId = $(this).closest('ul').closest('ul').find('input').first().val() + "_";

                        for(var i = 0; i<$data.length; i++){
                            if($data[i].id == $countryId){
                                add = false;
                                break;
                            }
                        }
                    }

                    if(add){
                        var $name1 = $(this).data('name');
                        $data.push({id: $id, text: $(this).data('name'), countryName: $name1, cityNameEn: $name1});
                        $destination.select2('data', $data);


                        $("#destination-el").data('selected', $data);
                        $destination.prev().show();

                        $destination.prev().find(".select2-search-field").css('height', '2px');
                        $destination.prev().find(".select2-search-choice").css('float', 'left');
                        $destination.prev().find(".select2-search-field").css('float', 'left');
                    }
                }
            });
        });

    }else{
        //Pre Select from bread crumbs
        var $place = $('ol.breadcrumb').find('li.active').text();
        $("#dropdown-destination").find('.city-list').find("input[type='checkbox']").each(function () {
            if($(this).data('name') == $place){
                $(this).attr('checked', true).change();

                var $destination = $("#destination-selected");
                var $data = $destination.select2('data');

                var $id = $(this).val();

                var add = true;
                if($(this).data('type') == 'country'){
                    //country
                    $id += "_";
                }else{
                    //city
                    //check parent
                    var $countryId = $(this).closest('ul').closest('ul').find('input').first().val() + "_";

                    for(var i = 0; i<$data.length; i++){
                        if($data[i].id == $countryId){
                            add = false;
                            break;
                        }
                    }
                }

                if(add){
                    var $name1 = $(this).data('name');
                    $data.push({id: $id, text: $(this).data('name'), countryName: $name1, cityNameEn: $name1});
                    $destination.select2('data', $data);


                    $("#destination-el").data('selected', $data);
                    $destination.prev().show();

                    $destination.prev().find(".select2-search-field").css('height', '2px');
                    $destination.prev().find(".select2-search-choice").css('float', 'left');
                    $destination.prev().find(".select2-search-field").css('float', 'left');
                }
            }
        });
    }




    //////////////////////////////////////////////////////////////////////////////////////////////

    $(".city-list").columnize({lastNeverTallest: true});


    //Pre Select departure
    var $from = $('.from-places');
    if($from.length > 0){
        var $name = $from.find('a').first().text();
        $("#dropdown-departure").find('.city-list').find("input[type='checkbox']").each(function () {
            if($(this).data('name') == $name){
                $(this).attr('checked', true).change();



                var $departure = $("#departure-selected");
                var $data = $departure.select2('data');

                var $id = $(this).val();

                var add = true;
                if($(this).data('type') == 'country'){
                    //country
                    $id += "_";
                }else{
                    //city
                    //check parent
                    var $countryId = $(this).closest('ul').closest('ul').find('input').first().val() + "_";

                    for(var i = 0; i<$data.length; i++){
                        if($data[i].id == $countryId){
                            add = false;
                            break;
                        }
                    }
                }

                if(add){
                    var $name1 = $(this).data('name');
                    $data.push({id: $id, text: $(this).data('name'), countryName: $name1, cityNameEn: $name1});
                    $departure.select2('data', $data);


                    $("#departure-el").data('selected', $data);
                    $departure.prev().show();

                    $departure.prev().find(".select2-search-field").css('height', '2px');
                    $departure.prev().find(".select2-search-choice").css('float', 'left');
                    $departure.prev().find(".select2-search-field").css('float', 'left');
                }

            }
        });
    }else{
        //Pre Select from preferred countries
        $("#dropdown-departure").find('.city-list').find(".city-near").find("input[type='checkbox']").attr('checked', true).eq(0).change();
        $("#dropdown-departure").find('.city-list').find(".city-near").find("input[type='checkbox']").each(function () {

            var $departure = $("#departure-selected");
            var $data = $departure.select2('data');

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
                    if($data[i].id == $countryId){
                        add = false;
                        break;
                    }
                }
            }

            if(add){
                var $name = $(this).data('name');
                $data.push({id: $id, text: $(this).data('name'), countryName: $name, cityNameEn: $name});
                $departure.select2('data', $data);


                $("#departure-el").data('selected', $data);
                $departure.prev().show();

                $departure.prev().find(".select2-search-field").css('height', '2px');
                $departure.prev().find(".select2-search-choice").css('float', 'left');
                $departure.prev().find(".select2-search-field").css('float', 'left');
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
            $.get('/app_dev.php/book-form/', function (responce) {
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

        $.getJSON("https://api.skypicker.com/api/v0.1/check_flights?flights="+$flightId+"&pnum="+$pnum+"&bnum="+$bnum+"&partner=picky", function (responce) {
            console.log(responce.flights_checked + " " + !responce.flights_invalid);
            if(responce.flights_checked && !responce.flights_invalid){
                //can book

                var price = responce.flights_price;

                $.getJSON('/app_dev.php/book-form/?price='+price+"&flights="+$flightId+"&"+$form.serialize(), function (postData) {
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
        if($(this).next().hasClass('skypicker-dropdown')){
            $(this).next().slideToggle().remove();
            return;
        }

        //show loading
        $('.loading').show();

        var $tr = $(this);

        var $id = $(this).data('itemid');
        var $filter = getFilter();

        var xhr = new XMLHttpRequest();
        xhr.open('POST', "http://api.travel.markmedia.fi/api/skypicker.fetch/" + $(this).data('from') + "/" + $(this).data('to'));
        //xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (this.readyState == 4) {

                var $row = '<tr class="skypicker-dropdown"><td colspan="6">';

                var $data = JSON.parse(this.responseText);

                for(var i = 0; i< $data.length; i++){
                    //var stops = $data[i].route.length - 1 ;

                    $row +=
                        '<div class="trip row">' +
                        '    <div class="col-xs-1 trip-duration">'+$data[i].dDate+'</div>' +

                        '    <div class="col-xs-9 trip-path">';

                    for(var j=0; j<$data[i].route.length; j++){
                        var duration = ($data[i].route[j].aTimeStamp - $data[i].route[j].dTimeStamp) / 60 ;//minutes
                        if(duration > 60){
                            var $hours = Math.floor(duration / 60 );//hours
                            var $mins = duration - $hours*60;
                            duration = $hours  + ":" + $mins + 'h';
                        }else{
                            duration += "min";
                        }


                        var $time = "";

                        if(j == 0){
                            $time = $data[i].route[j].dTime;
                        }
                        else if (j == $data[i].route.length-1){
                            $time = $data[i].route[j].aTime;
                        }
                        else if($data[i].route[j+1]) {//if has more put next depart time
                            $time = $data[i].route[j-1].aTime + " --- " + $data[i].route[j].dTime
                        }

                        $row +=
                            '<div class="trip-path-point">' +
                            '            <div class="trip-path-point-airport">'+$data[i].route[j].flyFrom+'</div>' + //cityFrom
                            '            <div class="trip-path-point-time">'+$data[i].route[j].dTime+

                            '</div>' +
                            '        </div>';

                        $row +=
                            '<div class="trip-path-spacer">' +
                            '            <div class="trip-path-spacer-label"><span data-original-title="'+$data[i].route[j].airline+'" data-toggle="tooltip" class="airline" style="background: url(&quot;/bundles/sandboxwebsite/img/airlines/'+$data[i].route[j].airline+'.gif&quot;) no-repeat scroll 0% 0% transparent;"></span>'+duration+'</div>' +
                            '            <div class="trip-path-spacer-arrow-wrapper trip-path-spacer-arrow-wrapper-init" style="width: 55px;">' +
                            '                                        <span class="trip-path-spacer-line">' +
                            '                                            <div></div>' +
                            '                                        </span>' +
                            '                <span class="trip-path-spacer-arrow"></span>' +
                            '            </div>' +
                            '        </div>';

                        $row +=
                            '<div class="trip-path-point">' +
                            '            <div class="trip-path-point-airport">'+$data[i].route[j].flyTo+'</div>' + //cityTo
                            '            <div class="trip-path-point-time">'+$data[i].route[j].aTime+

                            '</div>' +
                            '        </div>';

                        if($data[i].route[j+1]){//if has more put spacer

                            var durationWait = ( $data[i].route[j+1].dTimeStamp - $data[i].route[j].aTimeStamp) / 60 ;//minutes
                            if(durationWait > 60){
                                $hours = Math.floor(durationWait / 60 );//hours
                                $mins = durationWait - $hours*60;
                                durationWait = $hours  + ":" + $mins + 'h';
                            }else{
                                durationWait += "min";
                            }

                            $row +=
                                '<div class="trip-path-spacer">' +
                                '            <div class="trip-path-spacer-label">'+durationWait+'</div>' +
                                '            <div class="trip-path-spacer-arrow-wrapper trip-path-spacer-arrow-wrapper-init trip-path-spacer-arrow-layover" style="width: 30px;">' +
                                '                                        <span class="trip-path-spacer-line">' +
                                '                                            <div></div>' +
                                '                                        </span>' +
                                '                <span class="trip-path-spacer-arrow" style="display: none;"></span>' +
                                '            </div>' +
                                '        </div>';
                        }

                    }

                    $row +=
                        '    </div><div class="col-xs-2 trip-cost text-success">' +
                        '        <p>€'+$data[i].price+'</p>' +
                        '        <button class="btn btn-info trip-btn-cost">€'+$data[i].price+'</button>' +
                        '        <button class="btn btn-danger trip-btn-close">close</button>' +
                        '    </div>' +
                        '</div>';

                }

                $row += '</td></tr>';

                $tr.after($row);

                //hide loading
                $('.loading').hide();
            }
        };
        xhr.send(JSON.stringify($filter));

    });
    ////////////////////////////////////////////////////////////////////////////////////////////////


    //////////////////////////////////////////////////////////////////////////////////////////////////
    //SKYPICKER show route details
    $travelbaseItems.on('click', ".skypicker-route-details-toggle", function () {
        $(this).next(".skypicker-route-details").slideToggle();
    });
    //////////////////////////////////////////////////////////////////////////////////////////////////









    //var $destination_country = $("#edit-destination-country");
    //var $departureCity = $(".form-item-departure-city");




    //$destination_country.focus(cityPickerSlideDown);
    //$(".city-picker-list").click(cityPickerSlideDown);
    //$("#edit-departure-city").focus(cityPickerSlideDown);
    //$("#edit-company").focus(cityPickerSlideDown);
    //$departureCity.next(".city-picker-list").click(cityPickerSlideDown);

    //$destination_country.change();

///////set destination from city taxonomy term on node page
//    if (window.location.pathname != "/") {
//        $(".field-name-field-city").find("a").each(function () {
//            var $value = $(this).text();
//            $("#edit-destination-country").parent().find(".city-picker").find("input[type='checkbox']").each(function () {
//                if ($(this).data('name') == $value) {
//                    $(this).attr('checked', true).change();
//                    //$(this).closest(".form-item-destination-country").next(".city-picker-list").text($value);
//                }
//            });
//        });
//    }
///////set departure from "from" taxonomy term on node page
//    var $found = false;
//    if (window.location.pathname != "/") {
//        $(".field-name-field-from").find("a").each(function () {
//            var $value = $(this).text();
//            $("#edit-departure-city").parent().find(".city-picker").find("input[type='checkbox']").each(function () {
//                if ($(this).data('name') == $value) {
//                    $found = true;
//                    $(this).attr('checked', true).change();
//                    //$(this).closest(".form-item-departure-city").next(".city-picker-list").text($value);
//                }
//            });
//        });
//    }

    ////check prefered countries on homepage or when notfing is found
    //if (window.location.pathname == "/" || !$found) {
    //    $departureCity.find(".city-near").find("input[type='checkbox']").attr('checked', true).eq(0).change();
    //}

    //$(".form-type-date-popup > div").find("label").html("<img src='/sites/default/files/images/i-cal.png'>");


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
            $cityList.find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
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



    //$('.travelbase_top_menu').find("a").click(function () {
    //    var $target = $(this).data('target');
    //    $($target).focus();
    //    return false;
    //});



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


    //$form.find(':input').change(formChange);

    //$city_picker.find('.city-list').find(':input').change(formChange);
    //////////////////////////////////////////////////////////////////////////////
    //submit the form
    //$form.find(':input').eq(0).change();
    //////////////////////////////////////////////////////////////////////////////

    $("#form-submit").click(function (e) {
        e.preventDefault();
        formChange();
    }).click();
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
        $cityList.find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');

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

function formChange(e){
    getTable('.travelbase_items_df');
    setTimeout(function () {
        getTable(".travelbase_items_sp");
    }, 1000);

    //skyPickerImport();
}

function skyPickerImport(){
    var $progressbar = $(".progressbar");
    $progressbar.show();
    $progressbar.progressbar({
        value: false
    });
    var $filter = getFilter();
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'http://api.travel.markmedia.fi/api/skypicker.import/');
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

function getTable(container, reimport){
    $("#form-submit").addClass('disabled');
    if(!container) container = ".travelbase_items:visible";
    var type = 1;
    if(reimport === false) type = 2;

    var $filter = getFilter(container);
    $.post('/app_dev.php/api-filter/' + type, JSON.stringify($filter), function (responce) {
        var $travelbase_items = $(container);
        $travelbase_items.html(responce.html);

        if(responce.total > 0 && $travelbase_items.data('badge'))
            $($travelbase_items.data('badge')).text(responce.total);

        $travelbase_items.find('th a').click(function(){
            var $field = $(this).data('field');
            var $form = $('#travelbase-form');
            $form.find('input[name="order_field"]').val($field);

            var $direction = $form.find('input[name="order_direction"]');
            var  $directionVal = "";
            if($direction.val() == 'asc') $directionVal = 'desc';
            else $directionVal = 'asc';

            $direction.val($directionVal);
            getTable(null, false);
            //$("#edit-companies").change();
            return false;
        });

        $("#form-submit").removeClass('disabled');
    });
}


function cityPicker($el, $selected) {
    function repoFormatResult(repo) {
        var $title = repo.cityNameEn;
        if(repo.airportNameEn){
            $title += "("+repo.airportNameEn+", "+repo.airportCode+")";
        }else{
            $title += "("+repo.airportCode+")";
        }

        $title = '<div class="col-xs-10 col-xs-offset-2">' + $title +'</div>';

        if(repo.id.toString().indexOf("_") != -1){

            $title = '<div class="col-xs-12"><strong>' + repo.countryName +'</strong></div>';
        }

        var markup = '<div class="row">' + $title;

        markup += '</div>';

        return markup;
    }

    function repoFormatSelection(repo) {
        var $title = repo.countryName + "/" + repo.cityNameEn;

        if(repo.id.toString().indexOf("_") != -1){
            $title = repo.countryName;
        }
        repo.text = $title;
        var $data = $($selected).select2('data');
        $data.push(repo);
        $($selected).select2('data', $data);
        $($selected).prev().find(".select2-search-field").css('height', '2px');
        $($selected).prev().find(".select2-search-choice").css('float', 'none');
        $($selected).prev().find(".select2-search-field").css('float', 'none');
        $($selected).prev().find(".select2-search-field").css('overflow', 'hidden');
        //$("#departure-selected").prev().find(".select2-search-field").hide();

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




    $($el).select2({
        placeholder: "Search for a place",
        minimumInputLength: 3,
        multiple: true,
        width: '100%',
        dropdownAutoWidth: true,
        ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
            url: "http://api.travel.markmedia.fi/api/city.findByText/",
            dataType: 'json',
            quietMillis: 250,
            data: function (term, page) {
                return {
                    q: term // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                return {results: convertData(data)};
            },
            cache: true
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
                $.ajax("http://api.travel.markmedia.fi/api/city.findByText/?q=" + id, {
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

    $($el).on("select2-selecting", function (e) {
        $($selected).prev().css('display', 'block');
    });

    $($selected).select2({width: '100%'});
    $($selected).on('select2-removed', function (e) {
        var $elem = $($el);
        var selected = $elem.data('selected');

        var index = selected.indexOf(e.val.toString());
        if (index > -1) {
            selected.splice(index, 1);
        }
        $elem.data('selected', selected);

        if (selected.length == 0) {
            $($selected).prev().hide();
        }
    });

    $($selected).on('select2-focus', function (e) {
        e.preventDefault();
        console.log('focus');
        $($selected).prev().find(".select2-search-field").css('height', '2px');
        $($selected).prev().find(".select2-search-choice").css('float', 'left');
        $($selected).prev().find(".select2-search-field").css('float', 'left');

        $($el).prev().show();
        //$($el).select2('close');
        $($el).select2('open');
        $($el).prev().find('.select2-search-field').find('input').focus();

    });

    $($selected).on('select2-opening', function (e) {
        e.preventDefault();
        $($selected).select2('close');

        console.log('opening');
        $($el).prev().show();
        $($el).select2('close');
        $($el).select2('open');
        $($el).prev().find('.select2-search-field').find('input').focus();
    });

    $($el).on('select2-blur', function (e) {
        console.log('blur');

        if ($($selected).select2('data').length > 0) {
            console.log('hide me');
            //$($el).prev().hide();
        }

        $($selected).prev().find(".select2-search-choice").css('float', 'none');
        $($selected).prev().find(".select2-search-field").css('float', 'none');

    });

    $($el).on('select2-focus', function (e) {
        console.log('focus');

        //$($selected).prev().find(".select2-search-choice").css('float', 'left');
        //$($selected).prev().find(".select2-search-field").css('float', 'left');
    });

    $($selected).prev().hide();
}