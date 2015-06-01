function loadMoreOffers(){

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

    $('#club-badge').html("");
    var spinner = new Spinner(opts).spin($('#club-badge-loading')[0]);



    var $container = $("#offers-container");
    //remove load more btn
    $container.find('.load-more-offers-button').remove();
    var $offset = $container.find('.offer').length;

    var $offerType = $('#offertype').val();
    var $city = $('#offercity').val();
    var $country = $('#offercountry').val();

    $.get('/offers.get/?country='+$country+'&city='+$city+'&type='+$offerType+'&offset='+$offset+'&limit=' + $offerPerPageLimit, function (responce) {

        $container.append(responce.html);
        $('#club-badge').html(responce.total);

        if($container.find('.offer').length < responce.total){
            //show load more button
            $container.append('<div class="load-more-offers-button clear"><a href="#" class="btn btn-default" onclick="return loadMoreOffers()">Load more</a></div>');
        }

        spinner.stop();
    });

    return false;
}
function getFilter(container){
    if(!container) container = ".travelbase_items:visible";

    var $type = [];
    if($("#edit-only-flights").is(":checked")) $type.push(4);
    if($("#edit-only-hotel").is(":checked")) $type.push(6);
    if($("#edit-flight-and-hotel").is(":checked")) $type.push(1);
    if($("#edit-flight-and-hotel-unknown").is(":checked")) $type.push(2);

    var $dataFilter = $(container).data('filter-type');
    if($dataFilter){
        $type = $dataFilter;
    }

    if($(container).hasClass('travelbase_items_sp')){
        var $flyOneWay = $("#flyOneWay");
        var $flyWithReturn = $("#flyWithReturn");

        if($flyOneWay.is(":checked")) $type = [4];
        if($flyWithReturn.is(":checked")) $type = [3];

        if($flyOneWay.is(":checked") && $flyWithReturn.is(":checked")) $type = [3, 4];
    }



    var $form = $('#travelbase-form');

    var $field = $form.find('input[name="order_field"]').val();

    var $direction = $form.find('input[name="order_direction"]');

    var  $directionVal = $direction.val();

    var $destination_country = [];
    var $destination_city = [];
    var $departure_country = [];
    var $departure = [];

    $destData = $('#destination-dataholder').dataHolder('data');
    if($destData == undefined) $destData = [];
    for(var i=0;i<$destData.length; i++){
        var $id = $destData[i];
        if($id.toString().indexOf("_") != -1){
            //country
            $destination_country.push($id.replace("_", ""));
        }else{
            //city
            $destination_city.push($id);
        }
    }

    $depData = $('#departure-dataholder').dataHolder('data');
    for(var i=0;i<$depData.length; i++){
        var $id = $depData[i];
        if($id.toString().indexOf("_") != -1){
            //country
            $departure_country.push($id.replace("_", ""));
        }else{
            //city
            $departure.push($id);
        }
    }

    var $slider = $("#slider-range");
    var $sliderStopover = $("#slider-range-stopover");

    var $directFlight = 0;
    if($("#directFlight").is(":checked")) $directFlight = 1;

    var $sameDay = 0;
    if($("#sameDay").is(":checked")) $sameDay = 1;

    var $filter = {
        departure_country: $departure_country,
        departure: $departure,
        company: [ $("#edit-companies").val() ],
        type: $type,
        price: {
            min: $slider.slider( "values", 0 ),
            max: $slider.slider( "values", 1 )
        },
        stopoverfrom:	$sliderStopover.slider( "values", 0 )/60 + ":00",
        stopoverto:	$sliderStopover.slider( "values", 1 )/60 + ":00",
        destination_country: $destination_country,
        destination_city: $destination_city,
        directFlights: $directFlight,
        sameDay: $sameDay,
        limit: 10,
        offset: 0,
        orderField: $field,
        orderDirection: $directionVal
    };

    var $datepickFrom = $('#datepick-input-from');
    var $datepickTo = $('#datepick-input-to');

    var $date;

    var $fromDates = $datepickFrom.datepick('getDate');
    if($fromDates.length > 0){
        $date = new Date($fromDates[0]);
        $filter.date = { start: $date.toMysqlFormat() };
        $date = new Date($fromDates[1]);
        $filter.date.end = $date.toMysqlFormat();
    }
    var $toDates = $datepickTo.datepick('getDate');
    if($toDates.length > 0){
        $date = new Date($toDates[0]);
        $filter.date.returnFrom = $date.toMysqlFormat();
        $date = new Date($toDates[1]);
        $filter.date.returnTo = $date.toMysqlFormat();
    }

    return $filter;
}

function twoDigits(d) {
    if(0 <= d && d < 10) return "0" + d.toString();
    if(-10 < d && d < 0) return "-0" + (-1*d).toString();
    return d.toString();
}

Date.prototype.toMysqlFormat = function() {
    return this.getUTCFullYear() + "-" + twoDigits(1 + this.getUTCMonth()) + "-" + twoDigits(this.getUTCDate());// + " " + twoDigits(this.getUTCHours()) + ":" + twoDigits(this.getUTCMinutes()) + ":" + twoDigits(this.getUTCSeconds());
};

function loadMore(button){

    var $container = $(button).closest(".travelbase_items");
    var $tr = $container.find("> div > div ");

    if($container.find("> div").outerHeight() < 230 ){
        $container.find("> div").css('height', 'auto');

        if($tr.length >= $(button).data('total')){
            $(button).parent().hide();
        }
        return;
    }

    var $filter = getFilter();

    if($tr.length > 1){//1 because of header
        $filter.offset = $tr.length - 1 ;
    }



    $.post('/api-filter/0', JSON.stringify($filter), function (responce) {
        var $table = $container.find('> div ').eq(0);
        $table.append(responce.html);

        if($table.find('> div').length - 1 >= responce.total){
            //hide load more btn
            $table.find("button[id=loadMore]").parent().hide();
        }
    });

    var xhr = new XMLHttpRequest();
    xhr.open('POST', $api_url);
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (this.readyState == 4) {

            var $rows = '';
            var $data = JSON.parse(this.responseText);

            for(var i = 0; i< $data.items.length; i++){
                $rows += itemToRow($data.items[i]);
            }

            $rows += '<script>$(".my-popover").popover();</script><script></script>';
            var $table = $container.find('table');
            $table.append($rows);

            if($table.find('tr').length - 1 >= $data.total){
                //hide load more btn
                $(button).parent().hide();
            }


        }
    };
    //xhr.send(JSON.stringify($filter));
}


var $urlCache = [];
/**
 * @return {boolean}
 */
function UrlExists(url)
{
    if($.inArray(url, $urlCache) != -1){
        return false;
    }


    var http = new XMLHttpRequest();
    http.open('HEAD', url, false);
    http.send();

    if(http.status==404)
        $urlCache.push(url);

    return http.status!=404;
}

var $prevDate = "";
var $dateCount = 0;
function itemToRow($item){

    if(!$item.info) $item.info = "";
    if(!$item.duration) $item.duration = "";
    var $hotel = "Flight";
    if($item.hotel != false) $hotel= $item.hotel.name;

    if($hotel == "Flight" && ($item.duration == "" || $item.duration == "1")){
        $item.duration = 'One way';
    }

    if($hotel == 'Flyg och första hotellnatt på Phuket' || $hotel == 'Flyg och första hotellnatt i Ao Nang'){
        $hotel = 'Hotel with 1 night stay only';
    }

    var $date = $item.date.substr(8, 2) + "." + $item.date.substr(5, 2) + "." + $item.date.substr(2, 2);

    var $class = '';
    if($prevDate == '')
        $prevDate = $date;
    $dateCount++;
    if($prevDate != $date) {
        $prevDate = $date;
        if($dateCount > 5)
            $class = 'day-sep';
        $dateCount = 0;
    }

    var $company = $item.company.name;

    if($company == 'SkyPicker') $class += " skypicker-toggle";

    if(UrlExists("/sites/all/modules/travelbase/img/" + $item.company.name.toLowerCase() + ".png"))
        $company = "<img src='/sites/all/modules/travelbase/img/" + $item.company.name.toLowerCase() + ".png' alt='" + $item.company.name + "' />";


    var $lastCol = "<a href='" + $item.link + "'>" + $company + "</a>";

    if($item.company.name == 'SkyPicker'){
        $lastCol = "";
        for(var i=0;i<$item.airline.length; i++){
            $lastCol += "<img src='/bundles/sandboxwebsite/img/airlines/"+$item.airline[i]+".gif' title="+$item.airline[i]+" alt="+$item.airline[i]+">" ;
            break;
            if(i < $item.airline.length - 1) $lastCol += " ";
        }
    }

    return "<tr class='" + $class + "' data-itemid='"+ $item.id +"' >" +
    "<td>" + $date + "</td>" +
        //"<td>" + $company + "</td>" +
    "<td>" + $item.departure.cityNameFi + "</td>" +
    "<td>" + $item.destination.cityNameFi + "</td>" +
    "<td><a href='#' onclick='return false;' class='my-popover' data-toggle='popover' title='"+$hotel+"' data-content='"+$item.info+"' >" + $hotel + "</a></td>" +
        //"<td>" + $item.info + "</td>" +
    "<td>" + $item.duration + "</td>" +
    "<td>" + Math.round($item.price) + "</td>" +
    "<td>"+$lastCol+"</td>" +
    "</tr>";
}

function unCheckAll(a, e){
    e = e || window.event;
    e.preventDefault();
    e.stopPropagation();
    var $target = $(a).closest(".city-picker-dropdown").data('target');
    $($target).find('.city-list').find(":checkbox").prop("checked", 0).eq(2).change();
    //.find('.city-list').eq(0).find(":checkbox").prop("checked", 0).eq(2).change();
    //$("#body").css("margin-top", 0);
    return false;
}



function setField(){
    var $value = $("#content-wrapper").find('h1.page-title').text();
    var $found = false;
    //check in company
    $("#edit-companies").find('option').each(function(){
        if($(this).text() == $value){
            $(this).attr('selected', 1);
            $found = true;
        }
    });


    //check in city picker
    if(!$found){
        $("#edit-destination-country").parent().find(".city-picker").find("input[type='checkbox']").each(function () {
            if($(this).data('name') == $value){
                $(this).click();//.attr('checked', true);
                $(this).closest(".form-item-destination-country").next(".city-picker-list").html($value + " <a href='#' onclick='return unCheckAll(this, event)' class='form-list-close'><span>x</span></a>");
                //$("#edit-destination-country").val($value);
            }
        });
    }

    ////check in country
    //if(!$found){
    //    $("#edit-destination-country").find('option').each(function(){
    //        if($(this).text() == $value){
    //            $(this).attr('selected', 1);
    //            $found = true;
    //        }
    //    });
    //}

    ////check in city
    //if(!$found){
    //    $("#edit-destination-city").find('option').each(function(){
    //        if($(this).text() == $value){
    //            $(this).attr('selected', 1);
    //            $found = true;
    //        }
    //    });
    //}

}


var $travelbase_destination_value = '';
function fill_destination_country($list, $cityPicker) {
    var $destination_country = $list;//$(".city-picker-list");
    $destination_country.text();
    $travelbase_destination_value = "";

    listCrawler($cityPicker.find("> div.city-list > div > ul[class!='ignore']"));

    $travelbase_destination_value = $travelbase_destination_value.substring(0, $travelbase_destination_value.length - 2);
    if($travelbase_destination_value == "") $travelbase_destination_value = "All";
    if($travelbase_destination_value.length > 120) $travelbase_destination_value = $travelbase_destination_value.substr(0, 120) + "...";

    if($travelbase_destination_value != "All")
        $destination_country.html( " <a href='#' onclick='return unCheckAll(this, event)' class='form-list-close'><span>&times;</span></a>" + $travelbase_destination_value);
    else
        $destination_country.html($travelbase_destination_value);
}

function listCrawler($uls){
    //go through each ul
    $uls.each(function () {
        //get total amount of checkboxes in ul
        var $amount = $(this).find('input[type=checkbox]').length;
        //get total amount of selected checkboxes in ul
        var $selected = $(this).find('input[type=checkbox]:checked').length;

        //if all checkboxes are checked add only this name
        if($amount == $selected){
            var $value = $(this).find("label").first().text();
            if($value != ""){
                $travelbase_destination_value += $(this).find("label").first().text() + ", ";
            }

        }
        //if not all selected in list
        else if($selected > 0 && $selected < $amount){
            //go through all child li
            $(this).find(" > li").each(function () {
                //if has more sub lists go through then
                if($(this).hasClass("parent-list")){
                    listCrawler($(this).find(" > ul"));
                }
                //add to destination
                else if ($(this).find("> input[type=checkbox]:checked").length > 0){
                    $travelbase_destination_value += $(this).find("label").first().text() + ", ";
                }
            });
        }
    });
}

Array.prototype.unique = function() {
    var a = this.concat();
    for(var i=0; i<a.length; ++i) {
        for(var j=i+1; j<a.length; ++j) {
            if(a[i] === a[j])
                a.splice(j--, 1);
        }
    }

    return a;
};