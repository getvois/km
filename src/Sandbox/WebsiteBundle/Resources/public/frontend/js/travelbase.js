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

    // RANGE SLIDER(PRICE SLIDER)
    ////////////////////////////////////////////////////////////////////////////////////////////

    var $slider = $("#slider-range");
    $slider.slider({
        range: true,
        min: 0,
        max: 500,
        values: [ 75, 300 ],
        slide: function( event, ui ) {
            $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
        }
    });
    $( "#amount" ).val( "$" + $slider.slider( "values", 0 ) + " - $" + $slider.slider( "values", 1 ) );

    $slider.on( "slidechange", formChange );
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
                }
            });
        });

    }else{
        //Pre Select from bread crumbs
        var $place = $('ol.breadcrumb').find('li.active').text();
        $("#dropdown-destination").find('.city-list').find("input[type='checkbox']").each(function () {
            if($(this).data('name') == $place){
                $(this).attr('checked', true).change();
            }
        });    }




    //////////////////////////////////////////////////////////////////////////////////////////////

    $(".city-list").columnize();


    //Pre Select departure
    var $from = $('.from-places');
    if($from.length > 0){
        var $name = $from.find('a').first().text();
        $("#dropdown-departure").find('.city-list').find("input[type='checkbox']").each(function () {
            if($(this).data('name') == $name){
                $(this).attr('checked', true).change();
            }
        });
    }else{
        //Pre Select from preferred countries
        $("#dropdown-departure").find('.city-list').find(".city-near").find("input[type='checkbox']").attr('checked', true).eq(0).change();
    }




    ////////////////////////////////////////////////////////////////////////////////////////////////
    //SKYPICKER FETCH DATA
    var $travelbaseItems = $("#travelbase_items");
    $travelbaseItems.on('click', '.skypicker-toggle', function () {
        if($(this).next("tr").hasClass('skypicker-dropdown')){
            $(this).next("tr").slideToggle();
            return;
        }

        var $tr = $(this);

        var $id = $(this).data('itemid');
        var $filter = getFilter();
        var xhr = new XMLHttpRequest();
        xhr.open('GET', "http://api.travel.markmedia.fi/api/skypicker.fetch/" + $id);
        //xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (this.readyState == 4) {

                var $row = '<tr class="skypicker-dropdown"><td colspan="7"><table class="table">';

                var $data = JSON.parse(this.responseText);

                for(var i = 0; i< $data.length; i++){
                    var stops = $data[i].route.length - 1 ;
                    $row += '<tr class="skypicker-route-details-toggle"><td>departure: '+$data[i].dTime+'<br/>arrival: '+$data[i].aTime+'</td>';
                    $row += '<td>'+ stops  +' Stops<br/>'+$data[i].fly_duration+'</td>';
                    $row += '<td>'+$data[i].from.cityNameEn+'<br/>'+$data[i].to.cityNameEn +'</td>';
                    $row += '<td><a href="'+$data[i].deep_link+'">'+$data[i].price+'</a></td>';
                    $row += '</tr>';

                    $row += '<tr class="skypicker-route-details" style="display: none;"><td colspan="4"><table class="table">';

                    for(var j=0; j<$data[i].route.length; j++){
                        var duration = ($data[i].route[j].aTimeUTC - $data[i].route[j].dTimeUTC) / 60 ;//minutes
                        if(duration > 60){
                            var $hours = Math.floor(duration / 60 );//hours
                            var $mins = duration - $hours*60;
                            duration = $hours  + "h " + $mins + 'min';
                        }else{
                            duration += "min";
                        }

                        $row += '<tr><td>departure: '+$data[i].route[j].dTime+'<br/>arrival: '+$data[i].route[j].aTime+'</td>';
                        $row += '<td>'+ $data[i].route[j].airline  +'<br/>'+duration+'</td>';
                        $row += '<td>'+ $data[i].route[j].cityFrom  +'<br/>'+$data[i].route[j].cityTo+'</td>';
                        $row += '</tr>'
                    }
                    $row += '</table></td></tr>';
                }

                $row += '</table></tr>';

                $tr.after($row);
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



    $('.travelbase_top_menu').find("a").click(function () {
        var $target = $(this).data('target');
        $($target).focus();
        return false;
    });



    //TAXONOMY CONTENT TYPE FILTER SUBMIT ON CHANGE
    $("#block-system-main").on('change', ':input', function () {
        $(this).closest("form").find("#edit-submit-taxonomy-term").click();
    });




    $form.find(':input').change(formChange);
    $city_picker.find('.city-list').find(':input').change(formChange);
    //////////////////////////////////////////////////////////////////////////////
    //submit the form
        $form.find(':input').eq(0).change();
    //////////////////////////////////////////////////////////////////////////////
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
    getTable();
    skyPickerImport();
}

function skyPickerImport(){
    var $progressbar = $("#progressbar");
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
                $( "#progressbar").hide();

            }
        }
    };
    xhr.send(JSON.stringify($filter));
}

function getTable(){
    var $filter = getFilter();
    var xhr = new XMLHttpRequest();
    xhr.open('POST', $api_url);
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (this.readyState == 4) {
            var $field = $filter.orderField;

            var $table = '<table><tr>' +
                '<th><a href="#" data-field="date" '+ (($field == 'date')?'class="active"':"") + '>Date</a></th>' +
                    //'<th><a href="#" data-field="company">Company</a></th>' +
                '<th><a href="#" data-field="departure" '+ (($field == 'departure')?'class="active"':"") + '>From</a></th>' +
                '<th><a href="#" data-field="destination" '+ (($field == 'destination')?'class="active"':"") + '>To</a></th>' +
                '<th><a href="#" data-field="hotel" '+ (($field == 'hotel')?'class="active"':"") + '>Info</a></th>' +
                    //'<th><a href="#" data-field="info">Info</a></th>' +
                '<th><a href="#" data-field="duration" '+ (($field == 'duration')?'class="active"':"") + '>Duration</a></th>' +
                '<th><a href="#" data-field="price" '+ (($field == 'price')?'class="active"':"") + '>Price</a></th>' +
                '<th><a href="#" data-field="company" '+ (($field == 'company')?'class="active"':"") + '>Link</a></th></tr>';

            var $data = JSON.parse(this.responseText);

            for(var i = 0; i< $data.items.length; i++){
                $table += itemToRow($data.items[i]);
            }
            $table += '<script>$(".my-popover").popover();</script><script></script>';
            $table += '</table>';

            if($data.total > $data.items.length)//add load more button
                $table += '<button id="loadMore" onclick="loadMore()"><span class="fa fa-angle-double-down"></span></button>';


            if($data.items.length == 0){
                $table = "<div>No items found</div>";
            }

            var $travelbase_items = $("#travelbase_items");
            $travelbase_items.html($table);

            $travelbase_items.find('th a').click(function(){
                var $field = $(this).data('field');
                var $form = $('#travelbase-form');
                $form.find('input[name="order_field"]').val($field);

                var $direction = $form.find('input[name="order_direction"]');
                var  $directionVal = "";
                if($direction.val() == 'asc') $directionVal = 'desc';
                else $directionVal = 'asc';

                $direction.val($directionVal);

                $("#edit-companies").change();
                return false;
            });
        }
    };
    xhr.send(JSON.stringify($filter));
}
